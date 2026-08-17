<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Status;
use Cekta\Queue\Task;
use Cekta\Queue\TaskDTO;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use PDO;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class TaskRepository
{
    public function __construct(
        private PDO $pdo,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private string $table = "tasks",
        private string $queueName = "queue_default",
    ) {
    }

    /**
     * @param int $expiredSecond
     * @return Task[]
     */
    public function findExpired(int $expiredSecond): array
    {
        $sth = $this->pdo->prepare(
            <<<SQL
SELECT * FROM $this->table
WHERE 
    status = :status
    AND started_at < NOW() - make_interval(secs => :max);
SQL
        );
        $sth->execute([
            'status' => Status::PROCESSING->value,
            'max' => $expiredSecond,
        ]);
        $result = [];
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($sth as $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException('row must be an array');
            }
            $result[] = $this->toTask($row);
        }
        return $result;
    }

    public function findByUuid(string $uuid): ?Task
    {
        $sth = $this->pdo->prepare("SELECT * FROM tasks WHERE uuid = :uuid");
        $sth->execute(['uuid' => $uuid]);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }
        return $this->toTask($row);
    }

    public function push(string $uuid, JsonSerializable $message): void
    {
        $this->pdo->beginTransaction();
        $query = <<<SQL
INSERT INTO "$this->table"(
    "uuid",
    "queue_name",
    "fqcn",
    "payload",
    "created_at", 
    "status"
) VALUES (
    :uuid, 
    :queue_name,
    :fqcn,
    :payload,
    :created_at,
    :status
)
SQL;
        $sth = $this->pdo->prepare($query);
        $sth->execute([
            'uuid' => $uuid,
            'queue_name' => $this->queueName,
            'fqcn' => $message::class,
            'payload' => json_encode($message),
            'created_at' => $this->clock->now()->format(DateTimeInterface::RFC3339),
            'status' => Status::PENDING->value,
        ]);

        $sth = $this->pdo->prepare("INSERT INTO $this->queueName(uuid) VALUES (:uuid)");
        $sth->execute([
            'uuid' => $uuid,
        ]);

        $this->pdo->commit();
    }

    public function updateStatus(string $uuid, Status $status): void
    {
        $this->pdo->prepare("UPDATE tasks SET status = :status, finished_at = :finished_at WHERE uuid = :uuid")
            ->execute([
                'uuid' => $uuid,
                'status' => $status->value,
                'finished_at' => $this->clock->now()->format(DateTimeInterface::ATOM),
            ]);
        $this->logger->info("task {uuid} was handled status is {status}", [
            'uuid' => $uuid,
            'status' => Status::FAIL_STALE->value,
        ]);
    }

    public function findNext(): ?Task
    {
        $this->pdo->beginTransaction();
        $uuid = $this->findNextUUID();
        if ($uuid === null) {
            $this->pdo->commit();
            return null;
        }
        $task = $this->findByUuid($uuid);
        $this->pdo->commit();
        return $task;
    }

    /**
     * @param array<mixed> $row
     * @return Task
     */
    private function toTask(array $row): Task
    {
        /**
         * @var array{
         *  uuid: string,
         *  queue_name: string,
         *  fqcn: string,
         *  payload: string,
         *  created_at: string,
         *  started_at: ?string,
         *  finished_at: ?string,
         *  status: string
         *  } $row
         */
        try {
            return new TaskDTO(
                $row['uuid'],
                $row['fqcn'],
                json_decode($row['payload'], true),
                Status::from($row['status']),
                new DateTimeImmutable($row['created_at']),
                is_string($row['started_at']) ? new DateTimeImmutable($row['started_at']) : null,
                is_string($row['finished_at']) ? new DateTimeImmutable($row['finished_at']) : null,
            );
        } catch (DateMalformedStringException $e) {
            throw new RuntimeException(message: $e->getMessage(), previous: $e);
        }
    }

    private function findNextUUID(): ?string
    {
        $stm = $this->pdo->prepare(
            "DELETE FROM queue_default
                         WHERE uuid = (
                             SELECT uuid FROM queue_default
                             ORDER BY uuid
                             LIMIT 1
                             FOR UPDATE SKIP LOCKED
                         )
                         RETURNING uuid"
        );
        $stm->execute();
        $row = $stm->fetch();
        if (!is_array($row)) {
            return null;
        }
        /** @var array{uuid: string} $row */
        $this->pdo->prepare(
            "UPDATE tasks 
                        SET status = :status, 
                            started_at = :started_at,
                            started_hostname = :started_hostname,
                            started_pid = :started_pid
                        WHERE uuid = :uuid"
        )->execute([
            'uuid' => $row['uuid'],
            'status' => Status::PROCESSING->value,
            'started_at' => $this->clock->now()->format(DateTimeInterface::RFC3339),
            'started_hostname' => gethostname(),
            'started_pid' => getmypid(),
        ]);
        return $row['uuid'];
    }
}

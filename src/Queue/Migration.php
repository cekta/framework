<?php

namespace Cekta\Framework\Queue;

use Cekta\Queue\Status;
use PDO;

class Migration implements \Cekta\Migrator\Migration
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function up(): void
    {
        $pending = Status::PENDING->value;
        $processing = Status::PROCESSING->value;
        $success = Status::SUCCESS->value;
        $fail = Status::FAIL->value;
        $this->pdo->exec("CREATE TYPE job_status AS ENUM ('$pending', '$processing', '$success', '$fail')");
        $this->pdo->exec(
            "
CREATE TABLE tasks (
    uuid uuid PRIMARY KEY,
    queue_name VARCHAR(255) NOT NULL,
    handler TEXT NOT NULL,
    fqcn TEXT NOT NULL,
    payload jsonb NOT NULL,
    created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
    started_at timestamptz,
    started_hostname text,
    started_pid integer,
    finished_at timestamptz,
    status job_status DEFAULT 'pending' NOT NULL
)"
        );
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS queue_default (
            uuid uuid PRIMARY KEY DEFAULT uuidv7()
        )"
        );
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS tasks");
        $this->pdo->exec("DROP TABLE IF EXISTS queue_default");
        $this->pdo->exec("drop type if exists job_status");
    }

    public function priority(): int
    {
        return 1782079431;
    }
}

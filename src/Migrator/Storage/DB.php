<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Storage;

use Cekta\Framework\Migrator\Migration;
use Cekta\Framework\Migrator\Repository;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class DB implements Repository
{
    public function __construct(
        private PDO $pdo,
        private string $table_name = 'migrations',
        private string $column_id = 'id',
        private string $column_name = 'name',
    ) {
    }

    public function migrations(?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY {$this->column_id} desc";
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }
        $sth = $this->pdo->query($sql);
        if ($sth === false) {
            throw new PDOException(
                "[{$this->pdo->errorInfo()[0]}][{$this->pdo->errorInfo()[1]}] {$this->pdo->errorInfo()[2]}"
            );
        }

        $result = [];
        /** @var array<mixed> $row */
        foreach ($sth as $row) {
            if (
                !is_array($row)
                || !array_key_exists($this->column_name, $row)
                || !array_key_exists($this->column_id, $row)
                || !is_string($row[$this->column_name])
                || !class_exists($row[$this->column_name])
                || !is_int($row[$this->column_id])
            ) {
                throw new RuntimeException("`{$this->column_name}` or `{$this->column_id}` not found in row");
            }
            $result[$row[$this->column_id]] = $row[$this->column_name];
        }
        return $result;
    }

    public function up(Migration $migration): void
    {
        $migration->up();
        $sql = "INSERT INTO {$this->table_name} 
                ({$this->column_name}) 
                VALUES (?)";

        $sth = $this->pdo->prepare($sql);

        $sth->execute([get_class($migration)]);
    }

    public function isInstalled(): bool
    {
        try {
            $result = $this->pdo->query("SELECT 1 FROM {$this->table_name} LIMIT 1");
        } catch (Throwable) {
            return false;
        }
        return $result !== false;
    }

    public function install(): void
    {
        /** @var string $driver */
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $sql = match ($driver) {
            'mysql' => "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                {$this->column_id} BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                {$this->column_name}  VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            'pgsql' => "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                {$this->column_id} BIGSERIAL PRIMARY KEY,
                {$this->column_name}  VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            'sqlite' => "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                {$this->column_id} INTEGER PRIMARY KEY AUTOINCREMENT,
                {$this->column_name}  TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            default => throw new RuntimeException("Unsupported database driver: " . $driver),
        };

        $this->pdo->exec($sql);
    }

    public function down(int $id, Migration $migration): void
    {
        $migration->down();
        $sth = $this->pdo->prepare("DELETE FROM {$this->table_name} WHERE {$this->column_id} = ?");
        $sth->execute([$id]);
    }
}

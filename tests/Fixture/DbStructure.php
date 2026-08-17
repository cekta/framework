<?php

namespace Cekta\Framework\Test\Fixture;

use Cekta\Queue\Status;
use PDO;

class DbStructure
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function up(): void
    {
        $pending = Status::PENDING->value;
        $this->pdo->exec(
            sprintf(
                "CREATE TYPE job_status AS ENUM ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                Status::PENDING->value,
                Status::PROCESSING->value,
                Status::SUCCESS->value,
                Status::FAIL->value,
                Status::FAIL_STALE->value,
                Status::FAIL_EXCEPTION->value,
                Status::FAIL_HANDLER_NOT_FOUND->value,
                Status::FAIL_UNKNOWN->value
            )
        );
        $this->pdo->exec(
            "
CREATE TABLE tasks (
    uuid uuid PRIMARY KEY,
    queue_name VARCHAR(255) NOT NULL,
    fqcn TEXT NOT NULL,
    payload jsonb NOT NULL,
    created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
    started_at timestamptz,
    started_hostname text,
    started_pid integer,
    finished_at timestamptz,
    status job_status DEFAULT '$pending' NOT NULL
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
}

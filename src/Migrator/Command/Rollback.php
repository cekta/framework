<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Command;

use Cekta\Framework\Migrator\MigrationLocator;
use Cekta\Framework\Migrator\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Rollback extends Command
{
    private Repository $storage;
    private MigrationLocator $locator;

    public function __construct(
        Repository $storage,
        MigrationLocator $locator,
        string $name = 'migration:rollback'
    ) {
        parent::__construct($name);
        $this->storage = $storage;
        $this->locator = $locator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->storage->isInstalled()) {
            $output->writeln('migrator not installed');
            return Command::FAILURE;
        }
        $names = $this->storage->migrations(1);

        if (empty($names)) {
            $output->writeln('nothing to rollback');
            return Command::SUCCESS;
        }

        $output->writeln('start rollback');
        foreach ($names as $id => $fqcn) {
            $output->writeln("id is $id");
            $migration = $this->locator->get($fqcn);
            $class = get_class($migration);
            $output->writeln("{{$class} started");
            $this->storage->down($id, $migration);
            $output->writeln("{$class} finished");
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}

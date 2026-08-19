<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Command;

use Cekta\Framework\Migrator\Migration;
use Cekta\Framework\Migrator\MigrationLocator;
use Cekta\Framework\Migrator\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    /**
     * @var array<class-string>
     */
    private array $migrations;
    private Storage $storage;
    private MigrationLocator $locator;

    /**
     * @param Storage $storage
     * @param MigrationLocator $locator
     * @param array<class-string> ...$migrations
     * @param string $name
     */
    public function __construct(
        Storage $storage,
        MigrationLocator $locator,
        array $migrations,
        string $name = 'migrate',
    ) {
        parent::__construct($name);
        $this->storage = $storage;
        $this->locator = $locator;
        $this->migrations = $migrations;
    }

    protected function configure(): void
    {
        $this->addOption('install', 'i', description: 'Install persist storage if not installed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('install') && !$this->storage->isInstalled()) {
            $this->storage->install();
            $output->writeln('migration installed');
        }
        if (!$input->getOption('install') && !$this->storage->isInstalled()) {
            $output->writeln('migrator not installed');
            $output->writeln('use -i or --install to install');
            return Command::FAILURE;
        }
        $migrationNames = $this->storage->generateToExecute($this->migrations);

        if (empty($migrationNames)) {
            $output->writeln('nothing to migrate');
            return Command::SUCCESS;
        }

        $output->writeln('start');

        $migrations = [];
        foreach ($migrationNames as $fqcn) {
            $migrations[] = $this->locator->get($fqcn);
        }

        usort($migrations, function (Migration $a, Migration $b) {
            return $a->priority() <=> $b->priority();
        });

        foreach ($migrations as $migration) {
            $class = get_class($migration);
            $output->writeln("{$class} started");
            $this->storage->execute($migration);
            $output->writeln("{$class} executed");
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}

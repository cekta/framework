<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Command;

use Cekta\Framework\Migrator\TopologicalSorter;
use Cekta\Framework\Migrator\MigrationLocator;
use Cekta\Framework\Migrator\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    /**
     * @var array<class-string>
     */
    private array $migrations;
    private Repository $repository;
    private MigrationLocator $locator;

    /**
     * @param Repository $storage
     * @param MigrationLocator $locator
     * @param array<class-string> ...$migrations
     * @param string $name
     */
    public function __construct(
        Repository $storage,
        MigrationLocator $locator,
        array $migrations,
        string $name = 'migrate',
    ) {
        parent::__construct($name);
        $this->repository = $storage;
        $this->locator = $locator;
        $this->migrations = $migrations;
    }

    protected function configure(): void
    {
        $this->addOption('install', 'i', description: 'Install persist storage if not installed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('install') && !$this->repository->isInstalled()) {
            $this->repository->install();
            $output->writeln('migration installed');
        }
        if (!$input->getOption('install') && !$this->repository->isInstalled()) {
            $output->writeln('migrator not installed');
            $output->writeln('use -i or --install to install');
            return Command::FAILURE;
        }
        $migrationInstalled = $this->repository->migrations();
        $migrationNames = array_diff($this->migrations, $migrationInstalled);

        if (empty($migrationNames)) {
            $output->writeln('nothing to migrate');
            return Command::SUCCESS;
        }

        $output->writeln('start');
        
        $migrations = [];
        foreach ($migrationNames as $name) {
            $migrations[$name] = $this->locator->get($name);
        }

        $sort = new TopologicalSorter();
        $result = $sort(
            $migrationInstalled,
            $migrations,
        );

        foreach ($result as $name) {
            $output->writeln("{$name} started");
            $this->repository->up($migrations[$name]);
            $output->writeln("{$name} executed");
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}

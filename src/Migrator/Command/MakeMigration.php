<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigration extends Command
{
    private string $namespace;

    public function __construct(
        string $namespace = 'App\Migration',
        string $name = 'make:migration'
    ) {
        $this->namespace = $namespace;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('class_name', InputArgument::REQUIRED, 'class name')
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'overwrite default namespace',
                $this->namespace,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $namespace = $input->getOption('namespace');
        $class = $input->getArgument('class_name');

        ob_start();
        include __DIR__ . '/../../../template/migration.template.php';
        echo ob_get_clean();

        return Command::SUCCESS;
    }
}

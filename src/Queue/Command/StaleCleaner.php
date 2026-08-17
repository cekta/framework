<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('queue:stale-cleaner')]
class StaleCleaner extends Command
{
    private bool $shouldStop = false;

    public function __construct(
        private \Cekta\Queue\StaleCleaner $staleCleaner,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        pcntl_async_signals(true);
        $signalHandler = function () {
            $this->shouldStop = true;
        };
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGINT, $signalHandler);

        while (!$this->shouldStop) {
            $this->staleCleaner->clean();
        }
        return 0;
    }
}

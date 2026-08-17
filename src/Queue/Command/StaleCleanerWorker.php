<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue\Command;

use Cekta\Queue\StaleCleaner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('queue:stale-cleaner-worker')]
class StaleCleanerWorker extends Command
{
    private bool $shouldStop = false;

    public function __construct(
        private StaleCleaner $staleCleaner,
        private LoggerInterface $logger,
        private int $sleepSeconds = 60,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info("{command} started", ['command' => __CLASS__]);
        pcntl_async_signals(true);
        $signalHandler = function () {
            $this->logger->info("{command} signal handler started", ['command' => __CLASS__]);
            $this->shouldStop = true;
        };
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGINT, $signalHandler);

        while (!$this->shouldStop) {
            $this->staleCleaner->clean();
            sleep($this->sleepSeconds);
        }
        $this->logger->info("{command} graceful shutdown", ['command' => __CLASS__]);
        return 0;
    }
}

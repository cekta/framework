<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\ContainerFactory;
use Cekta\Framework\Dispatcher;
use Cekta\Migrator\Command\Migrate;
use Symfony\Component\Console\Application;

class Cli implements Dispatcher
{
    public function serve(ContainerFactory $container_factory): void
    {
        $container_factory->createContainer()
            ->get(Application::class)
            ->run();
    }
}

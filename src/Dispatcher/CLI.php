<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\CLI\Application;
use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CLI implements Dispatcher
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function dispatch(Project $project): void
    {
        /** @var Application $app */
        $app = $project->container()
            ->get(Application::class);
        $app->run();
    }
}

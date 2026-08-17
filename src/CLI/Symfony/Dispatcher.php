<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI\Symfony;

use Cekta\Framework\Project;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Dispatcher implements \Cekta\Framework\Dispatcher
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function dispatch(Project $project): int
    {
        /** @var Application $app */
        $app = $project->container()
            ->get(Application::class);
        return $app->run();
    }
}

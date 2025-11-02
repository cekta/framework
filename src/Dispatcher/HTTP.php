<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\Application;
use Cekta\Framework\Configuration;
use Cekta\Framework\ServiceProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

class HTTP
{
    public function serve(ServiceProvider $provider): void
    {
        $factory = new Psr17Factory();
        $worker = new PSR7Worker(Worker::create(), $factory, $factory, $factory);
        $configuration = Application::buildConfiguration($provider);
        $this->compileContainer($configuration);

        while ($request = $worker->waitRequest()) {
            /** @var ContainerInterface $container */
            $container = new ($configuration->container_fqcn)($configuration->params);
            try {
                /** @var RequestHandlerInterface $app */
                $app = $container->get(RequestHandlerInterface::class);
                $worker->respond($app->handle($request));
            } catch (\Throwable $e) {
                $worker->getWorker()->error((string)$e);
            }
        }
    }

    private function compileContainer(Configuration $configuration)
    {
        file_put_contents(
            $configuration->container_filename,
            new \Cekta\DI\Compiler(
                containers: $configuration->containers,
                params: $configuration->params,
                alias: $configuration->alias,
                fqcn: $configuration->container_fqcn,
            )->compile()
        );

        chmod($configuration->container_filename, 0777);

        echo "$configuration->container_filename was compiled" . PHP_EOL;
    }
}
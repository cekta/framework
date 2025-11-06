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
        $configuration->compile();

        while ($request = $worker->waitRequest()) {
            /** @var ContainerInterface $container */
            $container = $configuration->createContainer();
            try {
                /** @var RequestHandlerInterface $app */
                $app = $container->get(RequestHandlerInterface::class);
                $worker->respond($app->handle($request));
            } catch (\Throwable $e) {
                $worker->getWorker()->error((string)$e);
            }
        }
    }
}
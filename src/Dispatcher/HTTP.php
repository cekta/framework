<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\ContainerFactory;
use Cekta\Framework\Dispatcher;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

class HTTP implements Dispatcher
{
    public function serve(ContainerFactory $container_factory): void
    {
        $factory = new Psr17Factory();
        $worker = new PSR7Worker(Worker::create(), $factory, $factory, $factory);

        while ($request = $worker->waitRequest()) {
            /** @var ContainerInterface $container */
            $container = $container_factory->createContainer();
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
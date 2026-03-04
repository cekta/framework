<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use Throwable;

class HTTP implements Dispatcher
{
    public function dispatch(Project $project): int
    {
        $factory = new Psr17Factory();
        $worker = new PSR7Worker(Worker::create(), $factory, $factory, $factory);
        while ($request = $worker->waitRequest()) {
            $container = $project->container();
            try {
                /** @var RequestHandlerInterface $app */
                $app = $container->get(RequestHandlerInterface::class);
                $worker->respond($app->handle($request));
            } catch (Throwable $e) {
                $worker->getWorker()->error((string)$e);
            }
        }
        return 0;
    }
}

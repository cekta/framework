<?php

declare(strict_types=1);

namespace Cekta\Framework;

use ReflectionClass;
use Throwable;

readonly class DiscoverServiceProvider implements ServiceProvider
{
    /**
     * @param string[] $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    public function register(Application $app): Application
    {
        foreach ($this->items as $item) {
            if ($item == \Cekta\Framework\Pipeline::class) { // in_array ?
                continue; // ignore
            }
            try {
                $class = new ReflectionClass($item);
            } catch (Throwable $e) {
                continue;
            }
            $interface_names = $class->getInterfaceNames();
            if (in_array(\Psr\Http\Server\RequestHandlerInterface::class, $interface_names)) {
                $app->container($item);
            }
        }

        return $app;
    }
}

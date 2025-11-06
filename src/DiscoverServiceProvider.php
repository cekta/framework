<?php

declare(strict_types=1);

namespace Cekta\Framework;

use ReflectionClass;
use Throwable;

class DiscoverServiceProvider implements ServiceProvider
{
    private array $implement_excludes = [];
    private array $implements = [];

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
            try {
                $class = new ReflectionClass($item);
            } catch (Throwable $e) {
                continue;
            }
            $this->registerImplement($app, $class);
            
        }

        return $app;
    }

    /**
     * @param string $name
     * @param string[] $exclude
     * @return static
     */
    public function containerImplement(string $name, array $exclude = []): static
    {
        $this->implements[] = $name;
        $this->implement_excludes = array_unique(array_merge($this->implement_excludes, $exclude));
        return $this;
    }

    private function registerImplement(Application $app, ReflectionClass $class)
    {
        $interface_names = $class->getInterfaceNames();
        foreach ($this->implements as $implement)
        if (in_array($implement, $interface_names) && !in_array($class->getName(), $this->implement_excludes)) {
            $app->container($class->getName());
        }
    }
}

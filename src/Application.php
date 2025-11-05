<?php

declare(strict_types=1);

namespace Cekta\Framework;

class Application
{
    /**
     * @var string[]
     */
    private array $containers = [];
    /**
     * @var array<string, string>
     */
    private array $alias = [];
    /**
     * @var array<string, mixed>
     */
    private array $params = [];

    private string $container_filename = __DIR__ . '/../../../../runtime/Container.php';
    private string $container_fqcn = 'App\Runtime\Container';

    public static function buildConfiguration(ServiceProvider $app_provider): Configuration
    {
        $app = new self();
        $app = $app_provider->register($app);
        return new Configuration(
            container_filename: realpath($app->container_filename),
            container_fqcn: $app->container_fqcn,
            containers: $app->containers,
            params: $app->params,
            alias: $app->alias,
        );
    }

    public function container(string $container): static
    {
        $this->containers[] = $container;
        return $this;
    }

    public function alias(string $name, string $target): static
    {
        $this->alias[$name] = $target;
        return $this;
    }

    public function param(string $name, mixed $value): static
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function setContainerFilename(string $container_filename): Application
    {
        $this->container_filename = $container_filename;
        return $this;
    }

    public function setContainerFqcn(string $container_fqcn): Application
    {
        $this->container_fqcn = $container_fqcn;
        return $this;
    }
}

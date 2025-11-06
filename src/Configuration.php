<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Psr\Container\ContainerInterface;

readonly class Configuration
{
    public function __construct(
        public string $container_filename,
        public string $container_fqcn,
        public array $containers = [],
        public array $params = [],
        public array $alias = [],
    ) {
    }
    
    public function compile(): void
    {
        file_put_contents(
            $this->container_filename,
            new \Cekta\DI\Compiler(
                containers: $this->containers,
                params: $this->params,
                alias: $this->alias,
                fqcn: $this->container_fqcn,
            )->compile()
        );
    }
    
    public function createContainer(): ContainerInterface
    {
        return new ($this->container_fqcn)($this->params);
    }
}

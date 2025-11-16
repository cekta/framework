<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Cekta\DI\Lazy;

class ServiceProviderChain implements ServiceProvider
{
    /**
     * @var ServiceProvider[]
     */
    private array $providers;
    /**
     * @var array<string, mixed|Lazy>
     */
    private array $params = [];

    public function __construct(ServiceProvider ...$providers)
    {
        $this->providers = $providers;
    }

    public function params(): array
    {
        if (empty($this->params)) {
            foreach ($this->providers as $provider) {
                $this->params += $provider->params();
            }
        }
        return $this->params;
    }

    public function register(): array
    {
        $result = [
            'containers' => [],
            'alias' => [],
            'factories' => [],
            'singletons' => [],
        ];
        foreach ($this->providers as $provider) {
            $r = $provider->register();
            $result['containers'] = array_merge($result['containers'], $r['containers'] ?? []);
            $result['factories'] = array_merge($result['factories'], $r['factories'] ?? []);
            $result['singletons'] = array_merge($result['singletons'], $r['singletons'] ?? []);
            $result['alias'] += $r['alias'];
        }
        return $result;
    }
}

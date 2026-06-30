<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Cekta\DI\Exception\IntersectConfiguration;
use Cekta\Framework\Project\Effect;
use Cekta\Module\Module;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

/**
 * @external
 */
class ProjectDefault implements Project
{
    /**
     * @var array<string, string>
     */
    private array $cached_modules;
    /**
     * @var Closure(): iterable<ReflectionClass<object>>
     */
    private readonly Closure $class_loader;
    private Effect $effect;

    /**
     * @param array<Module> $modules
     * @param callable(): iterable<ReflectionClass<object>> $class_loader
     */
    public function __construct(
        private readonly array $modules,
        private readonly string $container_filename,
        private readonly string $container_fqcn,
        private readonly string $discover_filename,
        callable $class_loader,
        ?Effect $effect = null,
    ) {
        /** @noinspection PhpClosureCanBeConvertedToFirstClassCallableInspection */
        $this->class_loader = Closure::fromCallable($class_loader);

        if (empty($this->modules)) {
            throw new InvalidArgumentException('`modules` must be not empty');
        }

        $this->effect = $effect ?? new Effect();
    }

    public function container(bool $forceRecreate = false): ContainerInterface
    {
        if (
            $forceRecreate
            || !$this->effect->fileExists($this->discover_filename)
        ) {
            $this->discover();
        }

        $this->readDiscover();

        if (!$this->effect->fileExists($this->container_filename)) {
            $this->effect->write($this->container_filename, $this->buildContainer());
        }

        return $this->createContainer();
    }

    private function discover(): void
    {
        $data = $this->effect->encode(['modules' => $this->buildDiscover()], JSON_PRETTY_PRINT);
        if ($data === false) {
            throw new RuntimeException("Cant encode modules data to json");
        }
        $this->effect->write(
            $this->discover_filename,
            $data
        );
    }


    private function buildContainer(): string
    {
        $params = [
            'entries' => [],
            'alias' => [],
            'params' => [],
            'singletons' => [],
            'factories' => [],
        ];
        foreach ($this->modules as $key => $module) {
            $encoded_module = $this->cached_modules[$key];
            $r = $module->onBuildDefinitions($encoded_module);
            $r['params'] = $module->onCreateParameters($encoded_module);
            foreach (['params', 'alias'] as $key) {
                $record = $r[$key] ?? [];
                $params[$key] = [...$params[$key], ...$record];
            }

            foreach (['entries', 'singletons', 'factories'] as $key) {
                $params[$key] = [...$params[$key], ...($r[$key] ?? [])];
            }
        }
        /** @var array<string, string> $alias */
        $alias = $params['alias'];
        return $this->effect->buildContainer(
            $params['entries'],
            $params['params'],
            $alias,
            $this->container_fqcn,
            $params['singletons'],
            $params['factories']
        );
    }

    private function createContainer(): ContainerInterface
    {
        $params = [];
        foreach ($this->modules as $key => $module) {
            $record = $module->onCreateParameters($this->cached_modules[$key] ?? '');
            $intersect = array_intersect_key($params, $record);
            if (!empty($intersect)) {
                throw new IntersectConfiguration($intersect, 'params');
            }
            $params = [...$params, ...$record];
        }
        /** @var ContainerInterface $container */
        $container = $this->effect->createContainer($this->container_fqcn, $params);
        return $container;
    }

    /**
     * @return array<mixed>
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    private function buildDiscover(): array
    {
        $iterator = ($this->class_loader)();
        foreach ($iterator as $class) {
            foreach ($this->modules as $module) {
                $module->discover($class);
            }
        }
        $result = [];
        foreach ($this->modules as $module) {
            $data = $module->getCacheableData();
            if ($data === false) {
                $class = $module::class;
                throw new RuntimeException("data is not json_encodable for module: $class");
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * @return void
     */
    public function readDiscover(): void
    {
        $data = $this->effect->readJsonFile($this->discover_filename);
        if (
            !is_array($data)
            || !array_key_exists('modules', $data)
            || !is_array($data['modules'])
        ) {
            throw new InvalidArgumentException("$this->discover_filename must return array with modules array");
        }
        /** @var array{modules: array<string, string>} $data */
        $this->cached_modules = $data['modules'];
    }
}

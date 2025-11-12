<?php

declare(strict_types=1);

namespace Cekta\Framework;

use ReflectionClass;
use Throwable;

class ProjectDiscovery
{
    /**
     * @var array<string, string[]> key interface name, value excludes classes
     */
    private array $implements = [];
    /**
     * @var array<string, string[]> key class name, value excludes classes
     */
    private array $extends = [];

    private array $containers = [];
    /**
     * @var array<string, string[]>
     */
    private array $tags = [];
    /**
     * @var array<string, array{
     *     implement: string,
     *     excludes: string[],
     * }>
     */
    private array $tag_implements = [];
    /**
     * @var array<string, array{
     *     class: string,
     *     excludes: string[]
     * }>
     */
    private array $tag_extends = [];
    

    /**
     * @param string[] $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    /**
     * @param string $name
     * @param string[] $exclude
     * @return static
     */
    public function containerImplement(string $name, array $exclude = []): static
    {
        $this->implements[$name] = array_unique(array_merge($this->implements[$name] ?? [], $exclude));
        return $this;
    }

    /**
     * @param string $name
     * @param string[] $exclude
     * @return static
     */
    public function containerExtend(string $name, array $exclude = []): static
    {
        $this->extends[$name] = array_unique(array_merge($this->extends[$name] ?? [], $exclude));
        return $this;
    }

    /**
     * @return array{
     *     containers: string[],
     *     tags: array<string, string[]>
     * }
     */
    public function makeResult(): array
    {
        $this->check();

        return [
            'containers' => array_unique($this->containers),
            'tags' => $this->tags,
        ];
    }

    public function tagImplement(string $tag, string $implement, array $excludes = []): static
    {
        $this->tag_implements[$tag] = [
            'implement' => $implement,
            'excludes' => $excludes,
        ];

        return $this;
    }

    public function tagExtend(string $tag, string $class, array $excludes = []): static
    {
        $this->tag_extends[$tag] = [
            'class' => $class,
            'excludes' => $excludes,
        ];
        return $this;
    }

    private function check(): void
    {
        foreach ($this->items as $item) {
            try {
                $class = new ReflectionClass($item);
            } catch (Throwable $e) {
                continue;
            }
            $this->checkImplement($class);
            $this->checkExtend($class);
            $this->chectTags($class);
        }
    }
    
    private function checkImplement(ReflectionClass $class): void
    {
        $interface_names = $class->getInterfaceNames();
        foreach ($this->implements as $interface => $excludes) {
            if (in_array($interface, $interface_names) && !in_array($class->getName(), $excludes)) {
                $this->containers[] = $class->getName();
            }
        }
    }

    private function checkExtend(ReflectionClass $class): void
    {
        foreach ($this->extends as $name => $excludes) {
            if ($class->isSubclassOf($name) && !in_array($class->getName(), $excludes)) {
                $this->containers[] = $class->getName();
            }
        }
    }

    private function chectTags(ReflectionClass $class): void
    {
        $interface_names = $class->getInterfaceNames();
        foreach ($this->tag_implements as $tag => $record) {
            if (in_array($record['implement'], $interface_names) && !in_array($class->getName(), $record['excludes'])) {
                $this->tags[$tag][] = $class->getName();
            }
        }
        
        
        foreach ($this->tag_extends as $tag => $record) {
            if ($class->getName() instanceof $record['class'] && !in_array($class->getName(), $record['excludes'])) {
                $this->tags[$tag][] = $class->getName();
            }
        }
    }
}

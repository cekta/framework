<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Unit;

use Cekta\Framework\ProjectDefault;
use Cekta\Framework\Test\Unit\ProjectTest\ExampleModule;
use ReflectionClass;
use Testo\Assert;
use Testo\Lifecycle\AfterClass;

class ProjectDefaultTest
{
    #[AfterClass]
    public static function afterClass(): void
    {
        $reflection = new ReflectionClass(static::class);

        foreach ($reflection->getMethods() as $method) {
            foreach (
                [
                    __DIR__ . '/ProjectTest/' . $method->name . '.php',
                    __DIR__ . '/ProjectTest/' . ucfirst($method->name) . 'Container.php',
                ] as $filename
            ) {
                if (file_exists($filename)) {
                    unlink($filename);
                }
            }
        }
    }

    public function testFirstRunWithoutCache(): void
    {
        $container_filename = __DIR__ . '/ProjectTest/' . ucfirst(__FUNCTION__) . 'Container.php';
        $container_fqcn = 'Cekta\Framework\Test\Unit\ProjectTest\\' . ucfirst(__FUNCTION__) . 'Container';
        $discover_filename = __DIR__ . '/ProjectTest/' . __FUNCTION__ . '.php';
        $project = new ProjectDefault(
            [
                new ExampleModule([], [])
            ],
            $container_filename,
            $container_fqcn,
            $discover_filename,
            function () {
                return [];
            }
        );
        $project->container(); // check creation params
        $container_mtime = filemtime($container_filename);
        $discover_mtime = filemtime($discover_filename);
        Assert::int($container_mtime);
        Assert::int($discover_mtime);

        $project->container();
        Assert::same($container_mtime, filemtime($container_filename), 'container file must be reused');
        Assert::same($discover_mtime, filemtime($discover_filename), 'container file must be reused');
    }
}

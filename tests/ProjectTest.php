<?php

declare(strict_types=1);

namespace Cekta\Framework\Test;

use Cekta\Framework\Project;
use Cekta\Framework\Test\ProjectTest\ExampleModule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProjectTest extends TestCase
{
    public static function tearDownAfterClass(): void
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
        $container_fqcn = 'Cekta\Framework\Test\ProjectTest\\' . ucfirst(__FUNCTION__) . 'Container';
        $discover_filename = __DIR__ . '/ProjectTest/' . __FUNCTION__ . '.php';
        $project = new Project(
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
        Assert::assertNotFalse($container_mtime);
        Assert::assertNotFalse($discover_mtime);

        $project->container();
        Assert::assertSame($container_mtime, filemtime($container_filename), 'container file must be reused');
        Assert::assertSame($discover_mtime, filemtime($discover_filename), 'container file must be reused');
    }
}

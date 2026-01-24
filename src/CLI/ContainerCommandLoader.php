<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

class ContainerCommandLoader extends \Symfony\Component\Console\CommandLoader\ContainerCommandLoader
{
    public function __construct(ContainerInterface $container, array $commandMap)
    {
        parent::__construct($container, $commandMap);
    }

    public function get(string $name): Command
    {
        $command = parent::get($name);
        $command->setName($name);
        return $command;
    }
}

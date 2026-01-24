<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(ContainerCommandLoader $container_command_loader)
    {
        parent::__construct();;
        $this->setCommandLoader($container_command_loader);
    }
}

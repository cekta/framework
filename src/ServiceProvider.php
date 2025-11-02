<?php

declare(strict_types=1);

namespace Cekta\Framework;

interface ServiceProvider
{
    public function register(Application $app): Application;
}

<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Codecov\CodecovPlugin;
use Testo\Codecov\Report\PhpUnitXmlReport;
use Testo\Convention\NamingConventionPlugin;

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Преобразуем предупреждения (warnings), уведомления (notices) и ошибки пользователя (user-errors) в исключения
    if (in_array($errno, [E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    // Для фатальных ошибок продолжаем использовать стандартный обработчик
    return false;
});

return new ApplicationConfig(
    src: ["src"],
    suites: [
        new SuiteConfig(
            name: "Feature",
            location: ["tests/Feature"],
        ),
        new SuiteConfig(
            name: "Unit",
            location: ["tests/Unit"],
        ),
    ],
    plugins: [
        new NamingConventionPlugin(),
    ],
);

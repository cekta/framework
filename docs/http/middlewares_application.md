# Middlewares для маршрута

Можно регистрировать middlewares для всех http маршрутов приложения.

Например, логировать обращения или делать другую типовую работу.

**src/ExampleMiddleware.php** - расположить можно где угодно, главное следовать psr4.
```php
<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class ExampleMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('you can log request');
        $response = $handler->handle($request);
        $this->logger->info('you can log response');
        return $response;
    }
}

```

В файле **src/Application.php** при создании модуля ``\Cekta\Framework\HTTP\Module()``
можно указать имена middlewares для страницы 405, через параметр **middlewares**.

```php
// ...
new \Cekta\Framework\HTTP\Module(
    middlewares: [
        \App\ExampleMiddleware::class,
),
// ...
```

для применения изменений необходимо сделать build или restart

```
make restart
```

Теперь открывая **любой** маршрут будет вызываться на middleware.
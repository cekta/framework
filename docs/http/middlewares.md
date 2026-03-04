# Middlewares для маршрута

Можно регистрировать middlewares во время регистрации маршрута, эти middlewares будут вызываться каждый раз при 
обработке маршрута.

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

В php attribute маршрутов, есть параметр отвечающий за middlewares, можно там передавать необходимые middlewares.

```php
// ...
#[Route\GET(
    pattern: '/',
    middlewares: [
        \App\ExampleMiddleware::class
    ]
)]
// ...
```

полный пример: 

**src/Welcome.php**
```php
<?php

declare(strict_types=1);

namespace App;

use Cekta\Framework\HTTP\Response\JSONFactory;
use Cekta\Framework\HTTP\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route\GET(
    pattern: '/',
    middlewares: [
        \App\ExampleMiddleware::class
    ]
)]
final readonly class Welcome implements RequestHandlerInterface
{
    public function __construct(
        private JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create(['message' => 'welcome to cekta']);
    }
}

```

для применения изменений необходимо сделать build или restart

```
make restart
```

Теперь открывая маршруты будет вызываться наш middleware.
# Middlewares для страницы 404

Вы можете задавать middlewares для страницы 404.

Для этого необходимо:

1. Создать middleware или использовать существующую реализацию ``\Psr\Http\Server\MiddlewareInterface``.
2. Указать middlewares которые должны использоваться.

**src/Middleware404.php** расположить можно где угодно, главное следовать psr4.

```php
<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware404 implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // you can handle request here!!!
        return  $handler->handle($request);
    }
}

```

В файле **src/Application.php** при создании модуля ``\Cekta\Framework\HTTP\Module()``
можно указать имена middlewares для страницы 404, через параметр **middlewares_404**.

```php
// ...
new \Cekta\Framework\HTTP\Module(
    middlewares_404: [
        \App\Example404::class,
    ]
),
// ...
```

Перезапустим (минимум build) чтобы изменения вступили в силу.

```shell
make restart
```

Теперь при появлении страницы 404, будут вызываться указанные middlewares.
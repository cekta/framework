# Управление обработчиком страницы 404.

Вы можете сделать свою 404 страницу, для этого необходимо:

1. Создать обработчик (класс реализующий ``ServerRequestHandler``).
2. Использовать его для страницы 404.

**src/Example404.php** - расположить можно где угодно, главное следовать psr4.

```php
<?php

declare(strict_types=1);

namespace App;

use Cekta\Framework\HTTP\Response\JSONFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Example404 implements RequestHandlerInterface
{
    public function __construct(
        private JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create(['my custom page 404']);
    }
}
```

В файле **src/Application.php** при создании модуля ``\Cekta\Framework\HTTP\Module()``
можно задать обработчик для страницы 404 через параметр **handler_404**.

```php
// ...
new \Cekta\Framework\HTTP\Module(
    handler_404: \App\Example404::class,
),
// ...
```

Перезапустим (минимум build) чтобы изменения вступили в силу.

```shell
make restart
```

Теперь для обработки 404 страницы будет вызываться наш обработчик с json сообщением ``my custom page 404``.
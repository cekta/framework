# Управление обработчиком страницы 405.

Вы можете сделать свою 405 страницу, для этого необходимо:

1. Создать обработчик (класс реализующий ``ServerRequestHandler``).
2. Использовать его для страницы 405.

**src/Example405.php** - расположить можно где угодно, главное следовать psr4.

```php
<?php

declare(strict_types=1);

namespace App;

use Cekta\Framework\HTTP\Response\JSONFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Example405 implements RequestHandlerInterface
{
    public function __construct(
        private JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create([
            'message' => 'my custom page 405',
            ...$request->getAttributes(),
        ]);
    }
}
```

В файле **src/Application.php** при создании модуля ``\Cekta\Framework\HTTP\Module()``
можно задать обработчик для страницы 405 через параметр **handler_405**.

```php
// ...
new \Cekta\Framework\HTTP\Module(
    handler_405: \App\Example405::class,
),
// ...
```

Перезапустим (минимум build) чтобы изменения вступили в силу.

```shell
make restart
```

Теперь для обработки 405 страницы будет вызываться новый обработчик, с новым сообщением и деталями ошибки.
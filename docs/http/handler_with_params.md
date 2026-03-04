## Обработчик и параметры в url

При создании API мы хотим передавать параметры в url

```
GET /api/v1/items/{id}
```

В качестве маршрутизации
используется [fastroute](https://github.com/nikic/FastRoute?tab=readme-ov-file#defining-routes).
Можно пользоваться всеми возможностями задавая паттерн и регулярные выражения для значений.

Встреченные атрибуты можно получать с помощью ``$request->getAttribute('имя атрибута')``.

### Пример

**src/Example.php** - расположить можно где угодно, главное следовать psr4.

```php
<?php

declare(strict_types=1);

namespace App;

use Cekta\Framework\HTTP\Response\JSONFactory;
use Cekta\Framework\HTTP\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route\GET('/api/v1/items/{id:\d+}')]
final readonly class Example implements RequestHandlerInterface
{
    public function __construct(
        private JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create([
            'item_id' => $request->getAttribute('id'), 
        ]);
    }
}

```

```
make restart
```

Открываем [http://localhost:8080/api/v1/items/345](http://localhost:8080/api/v1/items/345)

Смотрим результат и видим номер 345 отправленный нами,
причем в качестве id могут быть только цифры (должно соответствовать regexp указанными при регистрации),
например [http://localhost:8080/api/v1/items/abc](http://localhost:8080/api/v1/items/abc) вернет 404.

Вы можете указывать свои регулярные выражения, если ничего не укажите то ``[^/]+`` (любое значение, кроме /).
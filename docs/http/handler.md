# Создание своего HTTP обработчика.

Создание HTTP обработчиков это одна из основных задач разработчика API.

1. Создайте класс (в любом месте) реализующий
   [\Psr\Http\Server\RequestHandlerInterface](https://github.com/php-fig/http-server-handler/blob/master/src/RequestHandlerInterface.php)
2. Используйте php
   attribute [\Cekta\Framework\HTTP\Route](https://github.com/cekta/framework/blob/master/src/HTTP/Route.php):
    1. **pattern** - url который должен обрабатываться
    2. **method** - http method (GET, POST, PATCH, ...) есть alias где его задавать не нужно
       [\Cekta\Framework\HTTP\Route\POST](https://github.com/cekta/framework/blob/master/src/HTTP/Route/POST.php)
       [\Cekta\Framework\HTTP\Route\DELETE](https://github.com/cekta/framework/blob/master/src/HTTP/Route/DELETE.php) и
       тд.  
       По умолчанию: ``GET``.
    3. **middlewares** -
       имена [psr/middleware](https://github.com/php-fig/http-server-middleware/blob/master/src/MiddlewareInterface.php)
       реализаций которые необходимо вызывать.  
       По умолчанию: ``[]``.
3. Сделайте build проекта
   ```
   make build
   ```
4. Можно открывать endpoint с указанным **method** и **pattern**.

В качестве примера обработчика можно
изучить [App\Welcome](https://github.com/cekta/skeleton/blob/master/src/Welcome.php)

Зависимости внедрять через autowiring в конструктор, они будут подгружаться автоматически, необходимые параметры будут
запрошены во время build.

## Параметры в url

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
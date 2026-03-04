# Создание своего HTTP обработчика.

Создание HTTP обработчиков это одна из основных задач разработчика API.

пример обработчика который обработает GET /example и выведет json ``{"message": "this is example}``.

**src/Example.php** - расположить можно где угодно, главное следовать psr4.

```php
declare(strict_types=1);

namespace App;

use Cekta\Framework\HTTP\Response\JSONFactory;
use Cekta\Framework\HTTP\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route\GET('/example')]
final readonly class Welcome implements RequestHandlerInterface
{
    public function __construct(
        private JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create(['message' => 'this is example']);
    }
}
```

**CLI**: нужно сделать build или полный перезапуск
```shell
make restart
```

**Проверяем успешный результат** открываем [GET http://localhost:8080/example](http://localhost:8080/example).

Зависимости внедрять через autowiring в конструктор, они будут подгружаться автоматически, необходимые параметры будут
запрошены во время build.

## Алгоритм в общем виде.

1. Создайте класс (в любом месте) реализующий
   [\Psr\Http\Server\RequestHandlerInterface](https://github.com/php-fig/http-server-handler/blob/master/src/RequestHandlerInterface.php)
2. Используйте php
   attribute [\Cekta\Framework\HTTP\Route](https://github.com/cekta/framework/blob/master/src/HTTP/Route.php):
    1. **pattern** - url который должен обрабатываться
    2. **method** - http method (GET, POST, PATCH, ...) есть alias где его задавать не нужно
       [\Cekta\Framework\HTTP\Route\POST](https://github.com/cekta/framework/blob/master/src/HTTP/Route/POST.php),
       [\Cekta\Framework\HTTP\Route\DELETE](https://github.com/cekta/framework/blob/master/src/HTTP/Route/DELETE.php) и
       тд.  
       По умолчанию: ``GET``.
    3. **middlewares** -
       имена [psr/middleware](https://github.com/php-fig/http-server-middleware/blob/master/src/MiddlewareInterface.php)
       реализаций которые необходимо вызывать.  
       По умолчанию: ``[]``.
3. Сделайте ``build`` проекта или ``restart``
   ```
   make restart
   ```
4. Можно открывать endpoint с указанным **method** и **pattern**.
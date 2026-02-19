# HTTP сервер

Создание API это одно из основных использований фреймворка.

Используются следующие решения:

1. [PSR-15: HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/) реализуете вы нужные приложению
   endpoints.
2. [CEKTA ROUTING](https://github.com/cekta/routing/) реализованно с помощью адаптера
   [cekta/routing-fastroute](https://github.com/cekta/routing-fastroute) для
   [nikic/fast-route](https://github.com/nikic/FastRoute).
3. [Roadrunner Server](https://roadrunner.dev/).

## Создание своего обработчика.

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

## Управлением страницей 404 (NOT FOUND)
## Управлением страницей 405 (NOT ALLOWED)
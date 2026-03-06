# Внедрение db (pgsql/mysql/sqlite)

Одна из самых распространенных зависимостей это реляционная БД, которая может быть представлена различными решениями:
pgsql, mysql, sqlite и тд.

Аналогичным способом можно внедрять:

1. очереди (kafka, rabbitmq и тд),
2. быстрые nosql базы (redis, dragonfly, valkey, memcached, mongadbи тд),
3. поисковые движки (opensearch, elasticksearch, sphinx и тд),
4. колоночные базы для аналитки (clickhouse и тд)
5. файловые хранилища s3 (minio и тд).

Рассмотрим максимально простой вариант по настройке postgres.

1. Определяемся с целями.

    1. В разработке, у каждого разработчика свой сервис postgres:18.
    2. В production, пусть это будет managed кластер, нам предоставляют credentials для входа.
    3. Возможность изменять credential после продуктовой сборки.
    4. В коде внедряем PDO как зависимость.
    5. Для работы PDO требуется extension pdo, pdo-pgsql.

2. Настрайваем docker image.

   **Dockerfile**
    ```dockerfile
   FROM php:8.4-cli-alpine AS base
   # ...
   RUN install-php-extensions \
     pdo \
     pdo_pgsql \
     # ...
   # ...
    ```

   Устанавливать надо именно в base чтобы эти extension были доступны как в dev, так и prod сборке.

3. Настрайваем сервисы для разработки.

   **docker-compose.yml**
   ```yaml
    services:
     app:
       # ...
       depends_on:
         db:
           condition: service_healthy
           
     db: 
       image: postgres:18-alpine3.22
       environment:
         - PGUSER=postgres # user for pg_isready
         - POSTGRES_PASSWORD=cekta
       ports:
         - "5432:5432"
       healthcheck:
         test: [ "CMD-SHELL", "pg_isready" ]
         interval: 10s
         timeout: 5s
         retries: 5
   ```

   Мы добавили новый сервис db, указали пароль ``cekta``, описали healthcheck, указали зависимость app от db.

4. Обновляем окружение разработки и запускаем его

    ```shell
   make refresh
   make dev
    ```

5. Используем зависимости в своем коде.

   **src/Test.php**
   ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    use Cekta\Framework\HTTP\Response\JSONFactory;
    use Cekta\Framework\HTTP\Route;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    
    #[Route\GET('/test')]
    final readonly class Test implements RequestHandlerInterface
    {
        public function __construct(
            private JSONFactory $factory,
            private \PDO $pdo,
        ) {
        }
    
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return $this->factory->create(
                $this->pdo->query('SHOW ALL')
                    ->fetchAll(\PDO::FETCH_ASSOC)
            );
        }
    }
   ```

   Он отображает текущие настройки postgres, так как для нормальной работы с бд надо инструмент миграций, для упрощения
   пока пропустим, сделаем это отдельно.

6. Настрайваем ``App\Module``.

   ```php
   // ...
    public function onCreateParameters(mixed $cachedData): array
    {
        return [
            \PDO::class . '$username' => $this->env['DB_USERNAME'] ?? 'postgres',
            \PDO::class . '$password' => $this->env['DB_PASSWORD'] ?? 'cekta',
            \PDO::class . '$dsn' => new \Cekta\DI\Lazy\Closure(function (ContainerInterface $c) {
                $host = $this->env['DB_HOST'] ?? 'db';
                $db = $this->env['DB_NAME'] ?? 'postgres';
                return "pgsql:host=$host;dbname=$db;";
            }),
        ];
    }
   ```
   Это production ready вариант конфигурации, чтобы была возможность изменять credential после релиза,
   при этом в разработке используются удобные параметры, небольшое объяснение:

   PDO зависит от 4 аргументов (dsn, username, password, options), один из аргументов обязательный (dsn), остальные
   опциональные (имеют значения по умолчанию).   
   Итого нам необходимо определить 3 параметра: dsn, username, password.

   ``\PDO::class . '$username'`` - для зависимости с именем ``\PDO::class`` аргумент с именем ``username``
   передать следующее значение, которое указано справа.

   ``this->env['DB_USERNAME'] ?? 'postgres'`` - если в environment указано DB_USERNAME то используем его,
   в остальных случаях используем значение по умолчанию ``postgres``.

   Обратите внимание на значение ``dsn``, если значение реализует интерфейс ``Cekta\DI\Lazy`` значит значение
   вычисляется после сборки в runtime, в данном случае значение генерируется callback функцией, которая генерирует
   dsn строку, на основе параметров из env или значений по умолчанию.

7. build или restart окружения.

    ```
    make restart
    ```

8. Проверяем результат.

   Открываем [http://localhost:8080/test](http://localhost:8080/test) - Получим текущие настройки postgres.
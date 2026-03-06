# Простейший пример guzzlehttp/guzzle

В любом проекте, однажды возникает необходимость во взаимодействии с внешними сервисами,
http самый распространенный протокол, давайте рассмотрим от самого простого случая к более сложным.

В мире PHP в качестве HTTP CLIENT используется [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle),
давайте рассмотрим пример его использования.

Данный пример внедрения можно рассматривать как простейший шаблон, меняя лишь целевой пакет и конфигурацию.

1. Устанавливаем необходимые пакеты.
    ```shell
    composer require guzzlehttp/guzzle
    ```

2. Используем зависимости в своем коде.
   **src/Welcome2** - расположение файла может быть любым, главное следовать psr4.
    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    use Cekta\Framework\HTTP\Route;
    use GuzzleHttp\Client;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    
    #[Route\GET('/w2')]
    final readonly class Welcome2 implements RequestHandlerInterface
    {
        public function __construct(
            private Client $client
        ) {
        }
    
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return $this->client->get('https://api.restful-api.dev/objects/7');
        }
    }
    ```

3. build или restart окружения.
    ```shell
    make restart
    ```

4. Настрайваем ``App\Module``. (опционально)

   При создании клиента, можно передать config для настройки клиента, если мы хотим их переопределить необходимо сделать
   следующее:

   **src/Module** - Конфигурация вашего приложения (**onCreateParameters**).
    ```php
    // ...
        public function onCreateParameters(mixed $cachedData): array
        {
            return [
                \GuzzleHttp\Client::class . '$config' => [
                    // Ваше желаемая конфигурация
                    'timeout'  => 2.0,
                ] 
            ];
        }
    // ...
    ```

   Запись вида ``\GuzzleHttp\Client::class . '$config'`` читается как -
   для зависимости с именем ``\GuzzleHttp\Client::class``
   в аргумент конструктора с именем ``$config`` отправить следующее указанное значение (массив с ключом timeout).

5. Проверяем результат.

   Откроем страницу [http://localhost:8080/w2](http://localhost:8080/w2) и наш запрос проксируется во внешнюю заглушку,
   ответ из которой продемонстрируется нам.


   
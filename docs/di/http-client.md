# Внедрение psr/http-client

Рассмотрим следующую ситуацию, вам необходимо взаимодействовать с внешним сервисом по HTTP, при этом вы не хотите
использовать конкретную реализацию, а хотите использовать
абстракцию [psr/http-client](https://www.php-fig.org/psr/psr-18/).

Данный пример можно рассматривать как самый распространенный шаблон внедрения,
меняя лишь названия пакетов и конфигурацию.

1. Устанавливаем необходимые пакеты.

    ```shell
    composer require psr/http-client guzzlehttp/guzzle psr/http-factory
    ```
   ``psr/http-client`` - нужная нам абстракция.  
   ``psr/http-factory`` - позволит абстрактно создавать запросы.  
   ``guzzlehttp/guzzle`` - конкретная реализация абстракций которую будем использовать.

2. Используем зависимости в своем коде.

   **src/Welcome3** - расположение может быть любым, главное следовать psr4.
    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    use Cekta\Framework\HTTP\Route;
    use Psr\Http\Client\ClientInterface;
    use Psr\Http\Message\RequestFactoryInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    
    #[Route\GET('/w3')]
    final readonly class Welcome3 implements RequestHandlerInterface
    {
        public function __construct(
            private ClientInterface $client,
            private RequestFactoryInterface $factory,
        ) {
        }
    
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return $this->client->sendRequest(
                $this->factory->createRequest('GET', 'https://api.restful-api.dev/objects/7')
            );
        }
    }
    ```
3. Настрайваем ``App\Module``.

   **src/Module.php** - в ``onBuildDefinitions`` укажем alias.
    ```php
   // ...
    public function onBuildDefinitions(mixed $cachedData): array
    {
        return [
            'alias' => [
                \Psr\Http\Client\ClientInterface::class => \GuzzleHttp\Client::class,
                \Psr\Http\Message\RequestFactoryInterface::class => \GuzzleHttp\Psr7\HttpFactory::class,
                // ...
            ],
            // ...
        ];
    }
    // ...
    ```

   Читается как тем кто запрашивает ``\Psr\Http\Message\RequestFactoryInterface::class``
   передавать зависимость ``\GuzzleHttp\Psr7\HttpFactory::class``.  
   Кто запрашивает ``\Psr\Http\Client\ClientInterface::class``
   передавать зависимость ``\GuzzleHttp\Client::class``.

4. build или restart окружения.
    ```shell
   make restart
   ```
8. Проверяем результат

   Открываем [http://localhost:8080/w3](http://localhost:8080/w3) убеждаемся что все работает.
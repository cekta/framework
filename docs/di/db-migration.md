# Внедрение миграций (db migration)

При работе с БД, хорошей практикой считается фиксировать изменения базы данных в миграция (изменения структуры БД).

В PHP существуют различные инструменты для управления миграциями, например
[doctrine/migrations](https://packagist.org/packages/doctrine/migrations), но мы не будем его использовать.

Продемонстрируем работу с **Module** (модулями), для этого мы используем
[cekta/migrator](https://packagist.org/packages/cekta/migrator).

1. Настройте [подключение к БД](db.md).
2. Устанавливаем необходимые пакеты.
   ```
   composer require cekta/migrator
   ```
3. Подключаем новые модули или настрайваемые существующие.

   **src/Application.php** добавим новый модуль в список.
   ```php
   // ..
   new \Cekta\Framework\CLI\Module( // существующий модуль, добавим команды.
     command_map: [
       'db:migrate' => \Cekta\Migrator\Command\Migrate::class,
       'db:rollback' => \Cekta\Migrator\Command\Rollback::class,
   ]),
   new \Cekta\Migrator\Module(), // новый модуль
   // ...
   ```

   Мы к нашему приложению подключили новый модуль,
   а в модуль по работе с CLI добавили две новый команды, имя которым можно задавать произвольное.

4. Используем зависимости в своем коде.

   **src/InitMigration.php** - располагать файл можно где угодно, главное следовать PSR4.
   ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    class InitMigration implements \Cekta\Migrator\Migration
    {
        public function __construct(private \PDO $pdo)
        {
        }
    
        public function up(): void
        {
            $this->pdo->exec('CREATE TABLE test1 (id int)');
        }
    
        public function down(): void
        {
            $this->pdo->exec('DROP TABLE test1');
        }
    
        /**
         * @inheritDoc
         */
        public static function id(): int
        {
            return 1772793536; // unix timestamp, date +%s 
        }
    }

   ```

5. build или restart окружения.
   ```
   make restart
   ```

6. ППроверяем результат.

   Выполним миграции.
   ```
   ./app.php db:migrate
   ```

   Откатим миграции.
   ```
   ./app.php db:rollback
   ```


Для удобства команды можно вынести в Makefile

**Makefile**
```
// ...
migrate:
docker compose run --rm -it app ./app.php db:migrate

rollback:
docker compose run --rm -it app ./app.php db:rollback
```

``db:migrate`` можно добавить в entrypoint dev контейнера, чтобы выполнять ее автоматически при включении,
но это уже по вкусу.
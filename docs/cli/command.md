# CLI Управление командами

Для работы с CLI на текущий момент используется [symfony/console](https://packagist.org/packages/symfony/console),
вы можете использовать документацию для этого компонента

## !!!Предупреждение!!!

На текущий момент для работы с cli командами используется
[symfony/console](https://packagist.org/packages/symfony/console) это практически стандарт де факто.

Мне не нравится эта реализация по следующим причинам:

1. Внутри команд каждый раз работа с reflection
2. Есть большое количество способов сделать одно и тоже разными путями для поддержки обратной совместимости (legacy).

Возможно в будущем реализация изменится и это потребует вам актуализировать, но пока we have what we have.

## Добавление вашей команды

1. Создайте класс расширяющий ``\Symfony\Component\Console\Command\Command``
2. Реализуйте метод ``protected function execute(InputInterface $input, OutputInterface $output): int``
3. Используйте php attribute ``\Symfony\Component\Console\Attribute\AsCommand`` чтобы указать имя команды.
4. Обновите конфигурацию
   ```
   make build # или make restart
   ```
5. Ваша команда доступна внутри app
   ```
   make shell
   ./app.php
   ```

В SKELETON идет [пример команды](https://github.com/cekta/skeleton/blob/master/src/HelloCommand.php)

Используйте оффицальную документацию по 
[symfony/console component](https://symfony.com/doc/current/components/console.html)
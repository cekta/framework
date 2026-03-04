# CLI: Создание команды.

Вы можете создавать свои команды и использовать их в приложение.

**src/HelloCommand.php** - расположить можно где угодно, главное следовать psr4.
```php
<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('hello', 'description')]
class HelloCommand extends Command
{
    /**
     * @inheritDoc
     */
    public function __construct(
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('log info message');
        $output->writeln('hello world');
        return Command::SUCCESS;
    }
}

```
**CLI**: нужно сделать build или полный перезапуск
```shell
make restart
```

**Проверим результат**
```
make shell # переход в консоль приложения
./app.php hello
hello world
```

Зависимости внедрять с помощью **__construct**.

Для работы с CLI на текущий момент используется [symfony/console](https://packagist.org/packages/symfony/console),
вы можете использовать [документацию для этого компонента](https://symfony.com/doc/current/components/console.html).

## !!!Предупреждение!!!

На текущий момент для работы с cli командами используется
[symfony/console](https://packagist.org/packages/symfony/console) это практически стандарт де факто.

Мне не нравится эта реализация по следующим причинам:

1. Внутри команд каждый раз работа с reflection
2. Есть большое количество способов сделать одно и тоже разными путями для поддержки обратной совместимости (legacy).

Возможно в будущем реализация изменится и это потребует вам актуализировать, но пока we have what we have.
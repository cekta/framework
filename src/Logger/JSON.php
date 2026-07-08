<?php

declare(strict_types=1);

namespace Cekta\Framework\Logger;

use Psr\Log\AbstractLogger;
use Stringable;

class JSON extends AbstractLogger
{
    /**
     * @inheritDoc
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        file_put_contents(
            'php://stdout',
            json_encode([
                'level' => $level,
                'message' => (string)$message,
                'context' => $context,
            ]) . PHP_EOL
        );
    }
}

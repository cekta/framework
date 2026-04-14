<?php

declare(strict_types=1);

namespace Cekta\Framework;

use DateInvalidTimeZoneException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Clock\ClockInterface;

class SystemClock implements ClockInterface
{
    public function __construct(private ?DateTimeZone $timezone = null)
    {
        if ($this->timezone === null) {
            $this->timezone = new DateTimeZone('UTC');
        }
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public static function fromSystemTimezone(): self
    {
        return new self(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * @throws Exception
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}

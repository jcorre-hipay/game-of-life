<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Time;

use GameOfLife\Domain\Time\ClockInterface;

class UtcClock implements ClockInterface
{
    /**
     * @return \DateTimeInterface
     */
    public function getCurrentDateTime(): \DateTimeInterface
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}

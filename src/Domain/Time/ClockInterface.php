<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Time;

interface ClockInterface
{
    /**
     * @return \DateTimeInterface
     */
    public function getCurrentDateTime(): \DateTimeInterface;
}

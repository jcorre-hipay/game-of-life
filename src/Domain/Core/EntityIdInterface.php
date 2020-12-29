<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Core;

interface EntityIdInterface
{
    /**
     * @return string
     */
    public function toString(): string;

    /**
     * @param EntityIdInterface $other
     * @return bool
     */
    public function equals(EntityIdInterface $other): bool;
}

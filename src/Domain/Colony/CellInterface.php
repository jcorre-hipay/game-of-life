<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;

interface CellInterface
{
    /**
     * @return bool
     */
    public function isLive(): bool;

    /**
     * @param string $state
     * @return CellInterface
     * @throws InvalidCellStateException
     */
    public function evolveTo(string $state): CellInterface;

    /**
     * @param string $state
     * @return bool
     */
    public function isInState(string $state): bool;

    /**
     * @return string
     */
    public function getState(): string;
}

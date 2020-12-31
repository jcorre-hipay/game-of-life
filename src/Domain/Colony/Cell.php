<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;

class Cell implements CellInterface
{
    private $state;

    /**
     * @param string $state
     * @throws InvalidCellStateException
     */
    public function __construct(string $state)
    {
        if (!\in_array($state, [CellState::LIVE, CellState::DEAD], true)) {
            throw new InvalidCellStateException(\sprintf('Cell state "%s" is not a valid one.', $state));
        }

        $this->state = $state;
    }

    /**
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->isInState(CellState::LIVE);
    }

    /**
     * @param string $state
     * @return CellInterface
     * @throws InvalidCellStateException
     */
    public function evolveTo(string $state): CellInterface
    {
        return new static($state);
    }

    /**
     * @param string $state
     * @return bool
     */
    public function isInState(string $state): bool
    {
        return $state === $this->state;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }
}

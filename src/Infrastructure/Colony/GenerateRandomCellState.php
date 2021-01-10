<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Colony;

use GameOfLife\Domain\Colony\CellState;

class GenerateRandomCellState implements GenerateCellStateInterface
{
    /**
     * @return string
     */
    public function execute(): string
    {
        $states = [
            CellState::LIVE,
            CellState::DEAD,
        ];

        return $states[\array_rand($states)];
    }
}

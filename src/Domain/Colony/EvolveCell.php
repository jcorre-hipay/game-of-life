<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

class EvolveCell implements EvolveCellInterface
{
    /**
     * @param CellInterface $cell
     * @param CellInterface[] $neighbours
     * @return string
     */
    public function execute(CellInterface $cell, array $neighbours): string
    {
        $liveNeighbourCount = 0;

        foreach ($neighbours as $neighbour) {
            if (!$neighbour->isLive()) {
                continue;
            }

            $liveNeighbourCount += 1;
        }

        if (3 === $liveNeighbourCount || (2 === $liveNeighbourCount && $cell->isLive())) {
            return 'live';
        }

        return 'dead';
    }
}

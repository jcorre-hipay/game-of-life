<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

interface EvolveCellInterface
{
    /**
     * @param CellInterface $cell
     * @param CellInterface[] $neighbours
     * @return string
     */
    public function execute(CellInterface $cell, array $neighbours): string;
}

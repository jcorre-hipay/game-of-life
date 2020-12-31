<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;

interface ColonyFactoryInterface
{
    /**
     * @param ColonyId $id
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     * @return ColonyInterface
     * @throws InvalidCellStateException
     * @throws InvalidColonyDimensionException
     */
    public function create(ColonyId $id, int $width, int $height, array $cellStates): ColonyInterface;
}

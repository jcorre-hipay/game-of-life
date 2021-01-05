<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Exception\InvalidGenerationException;

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

    /**
     * @param ColonyId $id
     * @param int $generation
     * @param int $width
     * @param int $height
     * @param array $cellStates
     * @return ColonyInterface
     * @throws InvalidCellStateException
     * @throws InvalidColonyDimensionException
     * @throws InvalidGenerationException
     */
    public function createAtGeneration(
        ColonyId $id,
        int $generation,
        int $width,
        int $height,
        array $cellStates
    ): ColonyInterface;
}

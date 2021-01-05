<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Exception\InvalidGenerationException;
use GameOfLife\Domain\Time\ClockInterface;

class ColonyFactory implements ColonyFactoryInterface
{
    private $clock;

    /**
     * @param ClockInterface $clock
     */
    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    /**
     * @param ColonyId $id
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     * @return ColonyInterface
     * @throws InvalidCellStateException
     * @throws InvalidColonyDimensionException
     */
    public function create(ColonyId $id, int $width, int $height, array $cellStates): ColonyInterface
    {
        return $this->createColony($id, 0, $width, $height, $cellStates);
    }

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
    ): ColonyInterface {
        $this->validateStrictlyPositiveGeneration($generation);

        return $this->createColony($id, $generation, $width, $height, $cellStates);
    }

    /**
     * @param ColonyId $id
     * @param int $generation
     * @param int $width
     * @param int $height
     * @param array $cellStates
     * @return Colony
     * @throws InvalidCellStateException
     * @throws InvalidColonyDimensionException
     */
    private function createColony(
        ColonyId $id,
        int $generation,
        int $width,
        int $height,
        array $cellStates
    ) {
        $this->validatePositiveNumber('width', $width);
        $this->validatePositiveNumber('height', $height);
        $this->validateCellCount($cellStates, $width * $height);

        return new Colony($this->clock, $id, $generation, $width, $height, $this->createCells($cellStates));
    }

    /**
     * @param string $dimension
     * @param int $value
     * @throws InvalidColonyDimensionException
     */
    private function validatePositiveNumber(string $dimension, int $value): void
    {
        if ($value > 0) {
            return;
        }

        throw new InvalidColonyDimensionException(
            \sprintf('The %s of a colony must be greater than 0, %d given.', $dimension, $value)
        );
    }

    /**
     * @param int $generation
     * @throws InvalidGenerationException
     */
    private function validateStrictlyPositiveGeneration(int $generation): void
    {
        if ($generation >= 0) {
            return;
        }

        throw new InvalidGenerationException(
            \sprintf('The generation of a colony must be greater or equal to 0, %d given.', $generation)
        );
    }

    /**
     * @param array $cells
     * @param int $count
     * @throws InvalidColonyDimensionException
     */
    private function validateCellCount(array $cells, int $count): void
    {
        if (\count($cells) === $count) {
            return;
        }

        throw new InvalidColonyDimensionException(
            \sprintf('Wrong number of cells, expected %d but got %d.', $count, \count($cells))
        );
    }

    /**
     * @param array $cellStates
     * @return array
     * @throws InvalidCellStateException
     */
    private function createCells(array $cellStates): array
    {
        $cells = [];

        foreach ($cellStates as $state) {
            $cells[] = new Cell($state);
        }

        return $cells;
    }
}

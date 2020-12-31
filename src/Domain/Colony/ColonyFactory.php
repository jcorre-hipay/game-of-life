<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
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
        $this->validatePositiveNumber('width', $width);
        $this->validatePositiveNumber('height', $height);
        $this->validateCellCount($cellStates, $width * $height);

        return new Colony($this->clock, $id, 0, $width, $height, $this->createCells($cellStates));
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

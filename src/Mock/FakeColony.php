<?php

declare(strict_types=1);

namespace GameOfLife\Mock;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Event\DomainEventInterface;

class FakeColony implements ColonyInterface
{
    private $id;
    private $width;
    private $height;
    private $cellStates;

    public function __construct(ColonyId $id, int $width, int $height, array $cellStates)
    {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->cellStates = $cellStates;
    }

    /**
     * @return ColonyId
     */
    public function getId(): ColonyId
    {
        return $this->id;
    }

    /**
     * @param DomainEventInterface[] $events
     * @return ColonyInterface
     */
    public function apply(array $events): ColonyInterface
    {
        return $this;
    }

    /**
     * @param EvolveCellInterface $evolveCell
     * @return DomainEventInterface[]
     */
    public function evolve(EvolveCellInterface $evolveCell): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function getGeneration(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return string[]
     */
    public function getCellStates(): array
    {
        return $this->cellStates;
    }
}

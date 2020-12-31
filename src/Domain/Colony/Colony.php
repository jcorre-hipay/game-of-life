<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Time\ClockInterface;

class Colony implements ColonyInterface
{
    private $clock;
    private $id;
    private $generation;
    private $width;
    private $height;
    private $cells;

    /**
     * @param ClockInterface $clock
     * @param ColonyId $id
     * @param int $generation
     * @param int $width
     * @param int $height
     * @param CellInterface[] $cells
     */
    public function __construct(
        ClockInterface $clock,
        ColonyId $id,
        int $generation,
        int $width,
        int $height,
        array $cells
    ) {
        $this->clock = $clock;
        $this->id = $id;
        $this->generation = $generation;
        $this->width = $width;
        $this->height = $height;
        $this->cells = $cells;
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
     * @throws InvalidCellStateException
     */
    public function apply(array $events): ColonyInterface
    {
        $generation = $this->generation;
        $cells = $this->cells;

        foreach ($events as $event) {
            if (!$this->id->equals($event->getEntityId())) {
                continue;
            }

            if ($event instanceof CellBorn) {
                $cells[$event->getIndex()] = $cells[$event->getIndex()]->evolveTo(CellState::LIVE);
                continue;
            }

            if ($event instanceof CellDied) {
                $cells[$event->getIndex()] = $cells[$event->getIndex()]->evolveTo(CellState::DEAD);
                continue;
            }

            if ($event instanceof GenerationEnded && $generation === $event->getGeneration()) {
                $generation += 1;
            }
        }

        return new static($this->clock, $this->id, $generation, $this->width, $this->height, $cells);
    }

    /**
     * @param EvolveCellInterface $evolveCell
     * @return DomainEventInterface[]
     */
    public function evolve(EvolveCellInterface $evolveCell): array
    {
        $now = $this->clock->getCurrentDateTime();

        $events = [];

        foreach ($this->cells as $index => $cell) {
            $nextState = $evolveCell->execute($cell, $this->getNeighbours($index));

            if ($cell->isInState($nextState)) {
                continue;
            }

            $events[] = $this->createCellStateSwitchedEvent($cell, $now, $index);
        }

        $events[] = new GenerationEnded($this->id, $now, $this->generation);

        return $events;
    }

    /**
     * @return int
     */
    public function getGeneration(): int
    {
        return $this->generation;
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
        return \array_map(
            function (CellInterface $cell): string {
                return $cell->getState();
            },
            $this->cells
        );
    }

    /**
     * @param int $index
     * @return CellInterface[]
     */
    private function getNeighbours(int $index): array
    {
        $currentCoordinates = [
            \intval(\floor($index / $this->width)),
            $index - \intval(\floor($index / $this->width)) * $this->width,
        ];

        $neighboursCoordinates = [
            [$currentCoordinates[0] - 1, $currentCoordinates[1] - 1],
            [$currentCoordinates[0] - 1, $currentCoordinates[1]],
            [$currentCoordinates[0] - 1, $currentCoordinates[1] + 1],
            [$currentCoordinates[0], $currentCoordinates[1] - 1],
            [$currentCoordinates[0], $currentCoordinates[1] + 1],
            [$currentCoordinates[0] + 1, $currentCoordinates[1] - 1],
            [$currentCoordinates[0] + 1, $currentCoordinates[1]],
            [$currentCoordinates[0] + 1, $currentCoordinates[1] + 1],
        ];

        $neighbours = [];

        foreach ($neighboursCoordinates as $coordinates) {
            if ($coordinates[0] < 0 || $coordinates[0] >= $this->height) {
                continue;
            }

            if ($coordinates[1] < 0 || $coordinates[1] >= $this->width) {
                continue;
            }

            $neighbours[] = $this->cells[$coordinates[0] * $this->width + $coordinates[1]];
        }

        return $neighbours;
    }

    /**
     * @param CellInterface $cell
     * @param \DateTimeInterface $date
     * @param int $index
     * @return DomainEventInterface
     */
    private function createCellStateSwitchedEvent(
        CellInterface $cell,
        \DateTimeInterface $date,
        int $index
    ): DomainEventInterface {
        return $cell->isLive() ? new CellDied($this->id, $date, $index) : new CellBorn($this->id, $date, $index);
    }
}

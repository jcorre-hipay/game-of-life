<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Event\DomainEventInterface;

interface ColonyInterface
{
    /**
     * @return ColonyId
     */
    public function getId(): ColonyId;

    /**
     * @param DomainEventInterface[] $events
     * @return ColonyInterface
     */
    public function apply(array $events): ColonyInterface;

    /**
     * @param EvolveCellInterface $evolveCell
     * @return DomainEventInterface[]
     */
    public function evolve(EvolveCellInterface $evolveCell): array;

    /**
     * @return int
     */
    public function getGeneration(): int;

    /**
     * @return int
     */
    public function getWidth(): int;

    /**
     * @return int
     */
    public function getHeight(): int;

    /**
     * @return string[]
     */
    public function getCellStates(): array;
}

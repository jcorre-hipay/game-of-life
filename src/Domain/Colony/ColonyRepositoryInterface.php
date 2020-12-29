<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Event\DomainEventInterface;

interface ColonyRepositoryInterface
{
    /**
     * @return ColonyId
     */
    public function nextId(): ColonyId;

    /**
     * @param string $id
     * @return ColonyId
     */
    public function getIdFromString(string $id): ColonyId;

    /**
     * @return ColonyInterface[]
     */
    public function findAll(): array;

    /**
     * @param ColonyId $id
     * @param int|null $generation
     * @return ColonyInterface|null
     */
    public function find(ColonyId $id, ?int $generation = null): ?ColonyInterface;

    /**
     * @param ColonyInterface $colony
     * @return ColonyCreated
     */
    public function add(ColonyInterface $colony): ColonyCreated;

    /**
     * @param ColonyId $id
     * @return ColonyDestroyed
     */
    public function remove(ColonyId $id): ColonyDestroyed;

    /**
     * @param DomainEventInterface[] $events
     * @return DomainEventInterface[]
     */
    public function commit(array $events): array;
}

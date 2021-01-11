<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;

interface ColonyRepositoryInterface
{
    /**
     * @return ColonyId
     * @throws RepositoryNotAvailableException
     */
    public function nextId(): ColonyId;

    /**
     * @param string $id
     * @return ColonyId
     * @throws RepositoryNotAvailableException
     */
    public function getIdFromString(string $id): ColonyId;

    /**
     * @return ColonyInterface[]
     * @throws RepositoryNotAvailableException
     */
    public function findAll(): array;

    /**
     * @param ColonyId $id
     * @param int|null $generation
     * @return ColonyInterface|null
     * @throws RepositoryNotAvailableException
     */
    public function find(ColonyId $id, ?int $generation = null): ?ColonyInterface;

    /**
     * @param ColonyId $id
     * @return int
     * @throws RepositoryNotAvailableException
     * @throws ColonyDoesNotExistException
     */
    public function getLastGeneration(ColonyId $id): int;

    /**
     * @param ColonyInterface $colony
     * @return ColonyCreated
     * @throws RepositoryNotAvailableException
     * @throws ColonyAlreadyExistsException
     */
    public function add(ColonyInterface $colony): ColonyCreated;

    /**
     * @param ColonyId $id
     * @return ColonyDestroyed
     * @throws RepositoryNotAvailableException
     * @throws ColonyDoesNotExistException
     */
    public function remove(ColonyId $id): ColonyDestroyed;

    /**
     * @param DomainEventInterface[] $events
     * @return DomainEventInterface[]
     * @throws RepositoryNotAvailableException
     */
    public function commit(array $events): array;
}

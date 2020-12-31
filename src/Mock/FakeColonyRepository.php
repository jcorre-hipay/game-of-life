<?php

declare(strict_types=1);

namespace GameOfLife\Mock;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Event\DomainEventInterface;

class FakeColonyRepository implements ColonyRepositoryInterface
{
    private static $colonies = [];

    /**
     * @return ColonyId
     */
    public function nextId(): ColonyId
    {
        return new ColonyId('59494a9a-32cc-481e-a4f1-093a8dcef162');
    }

    /**
     * @param string $id
     * @return ColonyId
     */
    public function getIdFromString(string $id): ColonyId
    {
        return new ColonyId($id);
    }

    /**
     * @return ColonyInterface[]
     */
    public function findAll(): array
    {
        return static::$colonies;
    }

    /**
     * @param ColonyId $id
     * @param int|null $generation
     * @return ColonyInterface|null
     */
    public function find(ColonyId $id, ?int $generation = null): ?ColonyInterface
    {
        if (!isset(static::$colonies[$id->toString()])) {
            return null;
        }

        return static::$colonies[$id->toString()];
    }

    /**
     * @param ColonyInterface $colony
     * @return ColonyCreated
     */
    public function add(ColonyInterface $colony): ColonyCreated
    {
        static::$colonies[$colony->getId()->toString()] = $colony;

        return new ColonyCreated($colony->getId(), new \DateTimeImmutable(), $colony->getCellStates());
    }

    /**
     * @param ColonyId $id
     * @return ColonyDestroyed
     */
    public function remove(ColonyId $id): ColonyDestroyed
    {
        if (isset(static::$colonies[$id->toString()])) {
            unset(static::$colonies[$id->toString()]);
        }

        return new ColonyDestroyed($id, new \DateTimeImmutable());
    }

    /**
     * @param DomainEventInterface[] $events
     * @return DomainEventInterface[]
     */
    public function commit(array $events): array
    {
        return [];
    }
}

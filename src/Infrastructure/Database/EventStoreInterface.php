<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Database;

use GameOfLife\Domain\Core\EntityIdInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Infrastructure\Exception\DataAccessException;

interface EventStoreInterface
{
    /**
     * @param DomainEventInterface[] $events
     * @throws DataAccessException
     */
    public function add(array $events): void;

    /**
     * @param EntityIdInterface $entityId
     * @return DomainEventInterface[]
     * @throws DataAccessException
     */
    public function find(EntityIdInterface $entityId): array;

    /**
     * @param EntityIdInterface $entityId
     * @throws DataAccessException
     */
    public function remove(EntityIdInterface $entityId): void;
}

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Database;

use GameOfLife\Domain\Core\EntityIdInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Infrastructure\Exception\DataAccessException;
use GameOfLife\Infrastructure\Exception\SerializationException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use GameOfLife\Infrastructure\Serializer\SerializerInterface;

class SqliteEventStore implements EventStoreInterface
{
    private $connection;
    private $serializer;
    private $logger;

    /**
     * @param ConnectionInterface $connection
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionInterface $connection,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param DomainEventInterface[] $events
     * @throws DataAccessException
     */
    public function add(array $events): void
    {
        foreach ($events as $event) {
            try {
                $this->insert($event);
            } catch (SerializationException $exception) {
                $this->logger->error(
                    'Fail to add event to the store.',
                    [
                        'exception' => $exception,
                        'event_type' => \get_class($event),
                        'entity_id' => $event->getEntityId()->toString(),
                    ]
                );
            }
        }
    }

    /**
     * @param EntityIdInterface $entityId
     * @return DomainEventInterface[]
     * @throws DataAccessException
     */
    public function find(EntityIdInterface $entityId): array
    {
        $results = $this
            ->connection
            ->query(
                'SELECT id, type, data FROM events WHERE entity_id = :entity_id ORDER BY date ASC, id ASC',
                [
                    'entity_id' => $entityId->toString(),
                ]
            );

        $events = [];

        foreach ($results as $result) {
            try {
                $events[] = $this->serializer->deserialize($result['data'], $result['type'], 'json');
            } catch (SerializationException $exception) {
                $this->logger->error(
                    'Fail to load event from the store.',
                    [
                        'exception' => $exception,
                        'event' => $result,
                    ]
                );
            }
        }

        return $events;
    }

    /**
     * @param EntityIdInterface $entityId
     * @throws DataAccessException
     */
    public function remove(EntityIdInterface $entityId): void
    {
        $this
            ->connection
            ->execute(
                'DELETE FROM events WHERE entity_id = :entity_id',
                [
                    'entity_id' => $entityId->toString()
                ]
            );
    }

    /**
     * @param DomainEventInterface $event
     * @throws DataAccessException
     * @throws SerializationException
     */
    private function insert(DomainEventInterface $event): void
    {
        $this
            ->connection
            ->execute(
                'INSERT INTO events (entity_id, type, date, data) VALUES (:entity_id, :type, :date, :data)',
                [
                    'entity_id' => $event->getEntityId()->toString(),
                    'type' => \get_class($event),
                    'date' => $event->getEventDate()->format(\DateTimeInterface::ISO8601),
                    'data' => $this->serializer->serialize($event, 'json'),
                ]
            );
    }
}

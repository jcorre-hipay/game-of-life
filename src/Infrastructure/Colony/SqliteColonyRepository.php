<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Colony;

use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Exception\InvalidGenerationException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use GameOfLife\Domain\Time\ClockInterface;
use GameOfLife\Infrastructure\Database\ConnectionInterface;
use GameOfLife\Infrastructure\Database\EventStoreInterface;
use GameOfLife\Infrastructure\Exception\DataAccessException;
use GameOfLife\Infrastructure\Identifier\GenerateEntityIdSeedInterface;
use GameOfLife\Infrastructure\Logger\LoggerInterface;

class SqliteColonyRepository implements ColonyRepositoryInterface
{
    private $eventStore;
    private $connection;
    private $factory;
    private $clock;
    private $generateId;
    private $logger;

    /**
     * @param EventStoreInterface $eventStore
     * @param ConnectionInterface $connection
     * @param ColonyFactoryInterface $factory
     * @param ClockInterface $clock
     * @param GenerateEntityIdSeedInterface $generateId
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ColonyFactoryInterface $factory,
        ClockInterface $clock,
        GenerateEntityIdSeedInterface $generateId,
        LoggerInterface $logger
    ) {
        $this->eventStore = $eventStore;
        $this->connection = $connection;
        $this->factory = $factory;
        $this->clock = $clock;
        $this->generateId = $generateId;
        $this->logger = $logger;
    }

    /**
     * @return ColonyId
     */
    public function nextId(): ColonyId
    {
        return $this->getIdFromString($this->generateId->execute());
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
     * @throws RepositoryNotAvailableException
     */
    public function findAll(): array
    {
        try {
            $results = $this->connection->query(
                'SELECT id, generation, width, height FROM colonies ORDER BY creation_date ASC'
            );

            $colonies = [];
            foreach ($results as $result) {
                $colonies[] = new ColonyProxy(
                    $this,
                    $this->getIdFromString($result['id']),
                    \intval($result['generation']),
                    \intval($result['width']),
                    \intval($result['height'])
                );
            }
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to find the colonies.', 0, $exception);
        }

        return $colonies;
    }

    /**
     * @param ColonyId $id
     * @param int|null $generation
     * @return ColonyInterface|null
     * @throws RepositoryNotAvailableException
     */
    public function find(ColonyId $id, ?int $generation = null): ?ColonyInterface
    {
        try {
            $events = $this->eventStore->find($id);

            if (empty($events)) {
                return null;
            }

            $colony = null;
            $toApply = [];
            $detectedGeneration = null;

            foreach ($events as $event) {
                if ($event instanceof ColonyCreated) {
                    $toApply = [];
                    $colony = $this->factory->createAtGeneration(
                        $id,
                        $event->getGeneration(),
                        $event->getWidth(),
                        $event->getHeight(),
                        $event->getCellStates()
                    );

                    if ($event->getGeneration() === $generation) {
                        return $colony;
                    }

                    continue;
                }

                if ($colony instanceof ColonyInterface) {
                    $toApply[] = $event;
                }

                if (null === $generation) {
                    continue;
                }

                if ($event instanceof GenerationEnded && $event->getGeneration() === $generation - 1) {
                    $detectedGeneration = $generation;
                    break;
                }
            }

            if ($generation !== $detectedGeneration) {
                return null;
            }

            return $colony->apply($toApply);
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to find the colony.', 0, $exception);
        } catch (InvalidCellStateException|InvalidColonyDimensionException|InvalidGenerationException $exception) {
            $this->logger->error(
                'Data corruption has been detected.',
                [
                    'exception' => $exception,
                    'colony_id' => $id->toString(),
                ]
            );

            return null;
        }
    }

    /**
     * @param ColonyId $id
     * @return int
     * @throws RepositoryNotAvailableException
     * @throws ColonyDoesNotExistException
     */
    public function getLastGeneration(ColonyId $id): int
    {
        try {
            $results = $this->connection->query(
                'SELECT generation FROM colonies WHERE id = :id',
                [
                    'id' => $id->toString(),
                ]
            );

            if (empty($results)) {
                throw new ColonyDoesNotExistException(
                    \sprintf(
                        'Cannot get the last generation of the colony %s because it does not exist.',
                        $id->toString()
                    )
                );
            }

            return \intval($results[0]['generation']);
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to get the last generation of the colony.', 0, $exception);
        }
    }

    /**
     * @param ColonyInterface $colony
     * @return ColonyCreated
     * @throws RepositoryNotAvailableException
     * @throws ColonyAlreadyExistsException
     */
    public function add(ColonyInterface $colony): ColonyCreated
    {
        try {
            if ($this->exists($colony->getId())) {
                throw new ColonyAlreadyExistsException(
                    \sprintf('Cannot add colony %s because it already exists.', $colony->getId()->toString())
                );
            }

            $event = new ColonyCreated(
                $colony->getId(),
                $this->clock->getCurrentDateTime(),
                $colony->getGeneration(),
                $colony->getWidth(),
                $colony->getHeight(),
                $colony->getCellStates()
            );

            $this
                ->connection
                ->execute(<<<EOQ
                    INSERT INTO colonies
                        (id, generation, width, height, creation_date, last_update_date)
                        VALUES 
                        (:id, :generation, :width, :height, :creation_date, :last_update_date)
                    EOQ,
                    [
                        'id' => $event->getEntityId()->toString(),
                        'generation' => $event->getGeneration(),
                        'width' => $event->getWidth(),
                        'height' => $event->getHeight(),
                        'creation_date' => $event->getEventDate()->format(\DateTimeInterface::ISO8601),
                        'last_update_date' => $event->getEventDate()->format(\DateTimeInterface::ISO8601),
                    ]
                );

            $this->eventStore->add([$event]);

            return $event;
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to add the colony.', 0, $exception);
        }
    }

    /**
     * @param ColonyId $id
     * @return ColonyDestroyed
     * @throws RepositoryNotAvailableException
     * @throws ColonyDoesNotExistException
     */
    public function remove(ColonyId $id): ColonyDestroyed
    {
        try {
            if (!$this->exists($id)) {
                throw new ColonyDoesNotExistException(
                    \sprintf('Cannot remove colony %s because it does not exist.', $id->toString())
                );
            }

            $this->connection->execute('DELETE FROM colonies WHERE id = :id', ['id' => $id->toString()]);

            $this->eventStore->remove($id);

            return new ColonyDestroyed($id, $this->clock->getCurrentDateTime());
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to remove the colony.', 0, $exception);
        }
    }

    /**
     * @param DomainEventInterface[] $events
     * @return DomainEventInterface[]
     * @throws RepositoryNotAvailableException
     */
    public function commit(array $events): array
    {
        $generations = [];
        $toCommit = [];

        foreach ($events as $event) {
            if (!$event->getEntityId() instanceof ColonyId) {
                continue;
            }

            $toCommit[] = $event;

            if ($event instanceof GenerationEnded) {
                $generations[$event->getEntityId()->toString()] = $event->getGeneration() + 1;
                continue;
            }

            if (!isset($generations[$event->getEntityId()->toString()])) {
                $generations[$event->getEntityId()->toString()] = '~';
            }
        }

        try {
            $this->eventStore->add($toCommit);

            $now = $this->clock->getCurrentDateTime()->format(\DateTimeInterface::ISO8601);

            foreach ($generations as $colonyId => $generation) {
                if ('~' === $generation) {
                    $this->connection->execute(
                        'UPDATE colonies SET last_update_date = :last_update_date WHERE id = :id',
                        [
                            'last_update_date' => $now,
                            'id' => $colonyId,
                        ]
                    );

                    continue;
                }

                $this->connection->execute(
                    'UPDATE colonies SET generation = :generation, last_update_date = :last_update_date WHERE id = :id',
                    [
                        'generation' => $generation,
                        'last_update_date' => $now,
                        'id' => $colonyId,
                    ]
                );
            }

            return $toCommit;
        } catch (DataAccessException $exception) {
            throw new RepositoryNotAvailableException('Fail to update the colonies.', 0, $exception);
        }
    }

    /**
     * @param ColonyId $id
     * @return bool
     * @throws DataAccessException
     */
    private function exists(ColonyId $id): bool
    {
        $results = $this->connection->query('SELECT id FROM colonies WHERE id = :id', ['id' => $id->toString()]);

        return !empty($results);
    }
}

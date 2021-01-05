<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Colony;

use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Core\EntityIdInterface;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use GameOfLife\Domain\Time\ClockInterface;
use GameOfLife\Infrastructure\Colony\ColonyProxy;
use GameOfLife\Infrastructure\Database\ConnectionInterface;
use GameOfLife\Infrastructure\Database\EventStoreInterface;
use GameOfLife\Infrastructure\Exception\DataAccessException;
use GameOfLife\Infrastructure\Identifier\GenerateEntityIdSeedInterface;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqliteColonyRepositorySpec extends ObjectBehavior
{
    function let(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ColonyFactoryInterface $colonyFactory,
        ClockInterface $clock,
        GenerateEntityIdSeedInterface $generateId,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($eventStore, $connection, $colonyFactory, $clock, $generateId, $logger);
    }

    function it_generates_a_new_colony_id(
        GenerateEntityIdSeedInterface $generateId
    ) {
        $generateId->execute()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->nextId()->shouldBeLike(new ColonyId('59494a9a-32cc-481e-a4f1-093a8dcef162'));
    }

    function it_translates_an_id_to_a_colony_id()
    {
        $this
            ->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')
            ->shouldBeLike(new ColonyId('59494a9a-32cc-481e-a4f1-093a8dcef162'));
    }

    function it_adds_a_new_colony_to_the_event_store(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ClockInterface $clock,
        ColonyInterface $colony,
        ColonyId $id
    ) {
        $now = new \DateTime('2020-09-04T09:03:14+0000');
        $clock->getCurrentDateTime()->willReturn($now);

        $colony->getId()->willReturn($id);
        $colony->getGeneration()->willReturn(0);
        $colony->getWidth()->willReturn(3);
        $colony->getHeight()->willReturn(1);
        $colony->getCellStates()->willReturn(['live', 'dead', 'live']);

        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id FROM colonies WHERE id = :id',
                [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->willReturn([]);

        $connection
            ->execute(<<<EOQ
                INSERT INTO colonies
                    (id, generation, width, height, creation_date, last_update_date)
                    VALUES 
                    (:id, :generation, :width, :height, :creation_date, :last_update_date)
                EOQ,
                [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                    'generation' => 0,
                    'width' => 3,
                    'height' => 1,
                    'creation_date' => '2020-09-04T09:03:14+0000',
                    'last_update_date' => '2020-09-04T09:03:14+0000',
                ]
            )
            ->shouldBeCalled();

        $eventStore
            ->add([new ColonyCreated($id->getWrappedObject(), $now, 0, 3, 1, ['live', 'dead', 'live'])])
            ->shouldBeCalled();

        $this
            ->add($colony)
            ->shouldBeLike(
                new ColonyCreated($id->getWrappedObject(), $now, 0, 3, 1, ['live', 'dead', 'live'])
            );
    }

    function it_throws_an_exception_when_trying_to_add_an_existing_colony(
        ConnectionInterface $connection,
        ColonyInterface $colony,
        ColonyId $id
    ) {
        $colony->getId()->willReturn($id);
        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id FROM colonies WHERE id = :id',
                [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->willReturn([['id' => '59494a9a-32cc-481e-a4f1-093a8dcef162']]);

        $this->shouldThrow(ColonyAlreadyExistsException::class)->during('add', [$colony]);
    }

    function it_finds_the_colony_at_the_last_generation_by_applying_its_domain_events(
        EventStoreInterface $eventStore,
        ColonyFactoryInterface $colonyFactory,
        ColonyInterface $generation0colony,
        ColonyInterface $generation3colony,
        ColonyId $id,
        ColonyCreated $colonyCreated,
        CellBorn $cellEvent1,
        GenerationEnded $generation0Ended,
        GenerationEnded $generation1Ended,
        CellDied $cellEvent2,
        GenerationEnded $generation2Ended
    ) {
        $colonyCreated->getEntityId()->willReturn($id);
        $colonyCreated->getGeneration()->willReturn(0);
        $colonyCreated->getWidth()->willReturn(3);
        $colonyCreated->getHeight()->willReturn(1);
        $colonyCreated->getCellStates()->willReturn(['live', 'dead', 'live']);

        $generation0Ended->getGeneration()->willReturn(0);
        $generation1Ended->getGeneration()->willReturn(1);
        $generation2Ended->getGeneration()->willReturn(2);

        $eventStore
            ->find($id)
            ->willReturn(
                [$colonyCreated, $cellEvent1, $generation0Ended, $generation1Ended, $cellEvent2, $generation2Ended]
            );

        $colonyFactory->createAtGeneration($id, 0, 3, 1, ['live', 'dead', 'live'])->willReturn($generation0colony);

        $generation0colony
            ->apply([$cellEvent1, $generation0Ended, $generation1Ended, $cellEvent2, $generation2Ended])
            ->shouldBeCalled()
            ->willReturn($generation3colony);

        $this->find($id)->shouldReturn($generation3colony);
    }

    function it_finds_the_colony_at_the_first_generation_by_detecting_the_creation_event(
        EventStoreInterface $eventStore,
        ColonyFactoryInterface $colonyFactory,
        ColonyInterface $generation0colony,
        ColonyId $id,
        ColonyCreated $colonyCreated,
        CellBorn $cellEvent1,
        GenerationEnded $generation0Ended,
        GenerationEnded $generation1Ended,
        CellDied $cellEvent2,
        GenerationEnded $generation2Ended
    ) {
        $colonyCreated->getEntityId()->willReturn($id);
        $colonyCreated->getGeneration()->willReturn(0);
        $colonyCreated->getWidth()->willReturn(3);
        $colonyCreated->getHeight()->willReturn(1);
        $colonyCreated->getCellStates()->willReturn(['live', 'dead', 'live']);

        $generation0Ended->getGeneration()->willReturn(0);
        $generation1Ended->getGeneration()->willReturn(1);
        $generation2Ended->getGeneration()->willReturn(2);

        $eventStore
            ->find($id)
            ->willReturn(
                [$colonyCreated, $cellEvent1, $generation0Ended, $generation1Ended, $cellEvent2, $generation2Ended]
            );

        $colonyFactory->createAtGeneration($id, 0, 3, 1, ['live', 'dead', 'live'])->willReturn($generation0colony);

        $this->find($id, 0)->shouldReturn($generation0colony);
    }

    function it_finds_the_colony_at_a_specific_generation_by_applying_its_domain_events(
        EventStoreInterface $eventStore,
        ColonyFactoryInterface $colonyFactory,
        ColonyInterface $generation0colony,
        ColonyInterface $generation2colony,
        ColonyId $id,
        ColonyCreated $colonyCreated,
        CellBorn $cellEvent1,
        GenerationEnded $generation0Ended,
        GenerationEnded $generation1Ended,
        CellDied $cellEvent2,
        GenerationEnded $generation2Ended
    ) {
        $colonyCreated->getEntityId()->willReturn($id);
        $colonyCreated->getGeneration()->willReturn(0);
        $colonyCreated->getWidth()->willReturn(3);
        $colonyCreated->getHeight()->willReturn(1);
        $colonyCreated->getCellStates()->willReturn(['live', 'dead', 'live']);

        $generation0Ended->getGeneration()->willReturn(0);
        $generation1Ended->getGeneration()->willReturn(1);
        $generation2Ended->getGeneration()->willReturn(2);

        $eventStore
            ->find($id)
            ->willReturn(
                [$colonyCreated, $cellEvent1, $generation0Ended, $generation1Ended, $cellEvent2, $generation2Ended]
            );

        $colonyFactory->createAtGeneration($id, 0, 3, 1, ['live', 'dead', 'live'])->willReturn($generation0colony);

        $generation0colony
            ->apply([$cellEvent1, $generation0Ended, $generation1Ended])
            ->shouldBeCalled()
            ->willReturn($generation2colony);

        $this->find($id, 2)->shouldReturn($generation2colony);
    }

    function it_returns_null_when_the_colony_cannot_be_found(
        EventStoreInterface $eventStore,
        ColonyId $id
    ) {
        $eventStore->find($id)->willReturn([]);

        $this->find($id)->shouldReturn(null);
    }

    function it_returns_null_when_the_colony_at_a_specific_generation_cannot_be_found(
        EventStoreInterface $eventStore,
        ColonyFactoryInterface $colonyFactory,
        ColonyInterface $generation0colony,
        ColonyId $id,
        ColonyCreated $colonyCreated,
        CellBorn $cellEvent1,
        GenerationEnded $generation0Ended,
        GenerationEnded $generation1Ended,
        CellDied $cellEvent2,
        GenerationEnded $generation2Ended
    ) {
        $colonyCreated->getEntityId()->willReturn($id);
        $colonyCreated->getGeneration()->willReturn(0);
        $colonyCreated->getWidth()->willReturn(3);
        $colonyCreated->getHeight()->willReturn(1);
        $colonyCreated->getCellStates()->willReturn(['live', 'dead', 'live']);

        $generation0Ended->getGeneration()->willReturn(0);
        $generation1Ended->getGeneration()->willReturn(1);
        $generation2Ended->getGeneration()->willReturn(2);

        $eventStore
            ->find($id)
            ->willReturn(
                [$colonyCreated, $cellEvent1, $generation0Ended, $generation1Ended, $cellEvent2, $generation2Ended]
            );

        $colonyFactory->createAtGeneration($id, 0, 3, 1, ['live', 'dead', 'live'])->willReturn($generation0colony);

        $this->find($id, 4)->shouldReturn(null);
    }

    function it_returns_null_when_the_colony_is_corrupted(
        EventStoreInterface $eventStore,
        ColonyFactoryInterface $colonyFactory,
        ColonyCreated $colonyCreated,
        ColonyId $id
    ) {
        $colonyCreated->getEntityId()->willReturn($id);
        $colonyCreated->getGeneration()->willReturn(0);
        $colonyCreated->getWidth()->willReturn(3);
        $colonyCreated->getHeight()->willReturn(1);
        $colonyCreated->getCellStates()->willReturn(['live', 'dead', 'live']);

        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $eventStore->find($id)->willReturn([$colonyCreated]);

        $colonyFactory
            ->createAtGeneration($id, 0, 3, 1, ['live', 'dead', 'live'])
            ->willThrow(new InvalidCellStateException('Oops'));

        $this->find($id)->shouldReturn(null);
    }

    function it_returns_proxy_colonies_when_finding_all_entities(
        ConnectionInterface $connection
    ) {
        $connection
            ->query('SELECT id, generation, width, height FROM colonies ORDER BY creation_date ASC')
            ->willReturn(
                [
                    [
                        'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                        'generation' => '42',
                        'width' => '16',
                        'height' => '9',
                    ]
                ]
            );

        $this
            ->findAll()
            ->shouldBeLike(
                [
                    new ColonyProxy(
                        $this->getWrappedObject(),
                        new ColonyId('59494a9a-32cc-481e-a4f1-093a8dcef162'),
                        42,
                        16,
                        9
                    )
                ]
            );
    }

    function it_removes_all_domain_events_related_to_a_colony(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ClockInterface $clock,
        ColonyId $id
    ) {
        $now = new \DateTime();
        $clock->getCurrentDateTime()->willReturn($now);

        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id FROM colonies WHERE id = :id',
                [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->willReturn(['id' => '59494a9a-32cc-481e-a4f1-093a8dcef162']);

        $connection
            ->execute('DELETE FROM colonies WHERE id = :id', ['id' => '59494a9a-32cc-481e-a4f1-093a8dcef162'])
            ->shouldBeCalled();

        $eventStore->remove($id)->shouldBeCalled();

        $this->remove($id)->shouldBeLike(new ColonyDestroyed($id->getWrappedObject(), $now));
    }

    function it_throws_an_exception_when_trying_to_remove_a_not_existing_colony(
        ConnectionInterface $connection,
        ColonyId $id
    ) {
        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id FROM colonies WHERE id = :id',
                [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->willReturn([]);

        $this->shouldThrow(ColonyDoesNotExistException::class)->during('remove', [$id]);
    }

    function it_commits_events_related_to_the_colonies(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ClockInterface $clock,
        ColonyId $id1,
        ColonyId $id2,
        EntityIdInterface $anotherEntityId,
        DomainEventInterface $event1,
        DomainEventInterface $event2,
        DomainEventInterface $event3,
        DomainEventInterface $event4
    ) {
        $now = new \DateTime('2020-09-04T09:03:14+0000');
        $clock->getCurrentDateTime()->willReturn($now);

        $id1->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');
        $id2->toString()->willReturn('4aea4bdb-c789-4945-8086-54bf22561c27');

        $event1->getEntityId()->willReturn($id1);
        $event2->getEntityId()->willReturn($id2);
        $event3->getEntityId()->willReturn($anotherEntityId);
        $event4->getEntityId()->willReturn($id1);

        $eventStore->add([$event1, $event2, $event4])->shouldBeCalled();

        $connection
            ->execute(
                'UPDATE colonies SET last_update_date = :last_update_date WHERE id = :id',
                [
                    'last_update_date' => '2020-09-04T09:03:14+0000',
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->shouldBeCalled();

        $connection
            ->execute(
                'UPDATE colonies SET last_update_date = :last_update_date WHERE id = :id',
                [
                    'last_update_date' => '2020-09-04T09:03:14+0000',
                    'id' => '4aea4bdb-c789-4945-8086-54bf22561c27',
                ]
            )
            ->shouldBeCalled();

        $this->commit([$event1, $event2, $event3, $event4])->shouldReturn([$event1, $event2, $event4]);
    }

    function it_update_the_colonies_generations_when_committing_events(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ClockInterface $clock,
        ColonyId $id,
        GenerationEnded $generationEnded
    ) {
        $now = new \DateTime('2020-09-04T09:03:14+0000');
        $clock->getCurrentDateTime()->willReturn($now);

        $generationEnded->getEntityId()->willReturn($id);
        $generationEnded->getGeneration()->willReturn(41);
        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $eventStore->add([$generationEnded])->shouldBeCalled();

        $connection
            ->execute(
                'UPDATE colonies SET generation = :generation, last_update_date = :last_update_date WHERE id = :id',
                [
                    'generation' => 42,
                    'last_update_date' => '2020-09-04T09:03:14+0000',
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                ]
            )
            ->shouldBeCalled();

        $this->commit([$generationEnded])->shouldReturn([$generationEnded]);
    }

    function it_throws_an_exception_when_the_sql_connection_is_down(
        EventStoreInterface $eventStore,
        ConnectionInterface $connection,
        ColonyInterface $colony,
        ColonyId $id,
        DomainEventInterface $event
    ) {
        $colony->getId()->willReturn($id);
        $event->getEntityId()->willReturn($id);
        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $eventStore->find(Argument::any())->willThrow(new DataAccessException('Oops'));
        $eventStore->add(Argument::any())->willThrow(new DataAccessException('Oops'));
        $connection->query(Argument::any(), Argument::any())->willThrow(new DataAccessException('Oops'));
        $connection->execute(Argument::any(), Argument::any())->willThrow(new DataAccessException('Oops'));

        $this->shouldThrow(RepositoryNotAvailableException::class)->during('add', [$colony]);
        $this->shouldThrow(RepositoryNotAvailableException::class)->during('find', [$id]);
        $this->shouldThrow(RepositoryNotAvailableException::class)->during('findAll', []);
        $this->shouldThrow(RepositoryNotAvailableException::class)->during('remove', [$id]);
        $this->shouldThrow(RepositoryNotAvailableException::class)->during('commit', [[$event]]);
    }
}

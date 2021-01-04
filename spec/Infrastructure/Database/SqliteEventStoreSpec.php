<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Database;

use GameOfLife\Domain\Core\EntityIdInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Infrastructure\Database\ConnectionInterface;
use GameOfLife\Infrastructure\Exception\SerializationException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use GameOfLife\Infrastructure\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqliteEventStoreSpec extends ObjectBehavior
{
    function let(
        ConnectionInterface $connection,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($connection, $serializer, $logger);
    }

    function it_inserts_domain_events_in_the_database(
        ConnectionInterface $connection,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        DomainEventInterface $event,
        EntityIdInterface $entityId
    ) {
        $entityId->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $event->getEntityId()->willReturn($entityId);
        $event->getEventDate()->willReturn(new \DateTime('2020-09-04T09:03:14+0000'));

        $serializer
            ->serialize($event, 'json')
            ->willReturn(
                '{"colony_id":{"id":"59494a9a-32cc-481e-a4f1-093a8dcef162"},"event_date":"2020-09-04T09:03:14+0000"}'
            );

        $connection
            ->execute(
                'INSERT INTO events (entity_id, type, date, data) VALUES (:entity_id, :type, :date, :data)',
                [
                    'entity_id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                    'type' => \get_class($event->getWrappedObject()),
                    'date' => '2020-09-04T09:03:14+0000',
                    'data' => '{"colony_id":{"id":"59494a9a-32cc-481e-a4f1-093a8dcef162"},'
                        .'"event_date":"2020-09-04T09:03:14+0000"}'
                ]
            )
            ->shouldBeCalled();

        $logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->add([$event]);
    }

    function it_drops_unserializable_domain_events_when_adding_events(
        SerializerInterface $serializer,
        LoggerInterface $logger,
        DomainEventInterface $event,
        EntityIdInterface $entityId
    ) {
        $entityId->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $event->getEntityId()->willReturn($entityId);
        $event->getEventDate()->willReturn(new \DateTime('2020-09-04T09:03:14+0000'));

        $serializer->serialize($event, 'json')->willThrow(SerializationException::class);

        $logger->error('Fail to add event to the store.', Argument::any())->shouldBeCalled();

        $this->add([$event]);
    }

    function it_find_all_domain_events_related_to_an_entity_order_by_date(
        ConnectionInterface $connection,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        EntityIdInterface $entityId,
        DomainEventInterface $event
    ) {
        $entityId->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id, type, data FROM events WHERE entity_id = :entity_id ORDER BY date ASC, id ASC',
                [
                    'entity_id' => '59494a9a-32cc-481e-a4f1-093a8dcef162'
                ]
            )
            ->willReturn(
                [
                    [
                        'id' => 42,
                        'type' => 'GameOfLife\Domain\Event\ColonyDestroyed',
                        'data' => '{"colony_id":{"id":"59494a9a-32cc-481e-a4f1-093a8dcef162"},'
                            .'"event_date":"2020-09-04T09:03:14+0000"}'
                    ]
                ]
            );

        $serializer
            ->deserialize(
                '{"colony_id":{"id":"59494a9a-32cc-481e-a4f1-093a8dcef162"},"event_date":"2020-09-04T09:03:14+0000"}',
                'GameOfLife\Domain\Event\ColonyDestroyed',
                'json'
            )
            ->willReturn($event);

        $logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->find($entityId)->shouldReturn([$event]);
    }

    function it_drops_unserializable_domain_events_when_finding_events(
        ConnectionInterface $connection,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        EntityIdInterface $entityId
    ) {
        $entityId->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->query(
                'SELECT id, type, data FROM events WHERE entity_id = :entity_id ORDER BY date ASC, id ASC',
                [
                    'entity_id' => '59494a9a-32cc-481e-a4f1-093a8dcef162'
                ]
            )
            ->willReturn(
                [
                    [
                        'id' => 42,
                        'type' => 'GameOfLife\Domain\Event\ColonyDestroyed',
                        'data' => '{"colony_id":{"id":"59494a9a-32cc-48',
                    ]
                ]
            );

        $serializer
            ->deserialize('{"colony_id":{"id":"59494a9a-32cc-48', 'GameOfLife\Domain\Event\ColonyDestroyed', 'json')
            ->willThrow(SerializationException::class);

        $logger->error('Fail to load event from the store.', Argument::any())->shouldBeCalled();

        $this->find($entityId)->shouldReturn([]);
    }

    function it_removes_all_domain_events_related_to_an_entity(
        ConnectionInterface $connection,
        EntityIdInterface $entityId
    ) {
        $entityId->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $connection
            ->execute(
                'DELETE FROM events WHERE entity_id = :entity_id',
                [
                    'entity_id' => '59494a9a-32cc-481e-a4f1-093a8dcef162'
                ]
            )
            ->shouldBeCalled();

        $this->remove($entityId);
    }
}

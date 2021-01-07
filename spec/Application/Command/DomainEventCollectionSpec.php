<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Command;

use GameOfLife\Application\Exception\CollectionIsImmutableException;
use GameOfLife\Application\Exception\UnsupportedCollectionItemTypeException;
use GameOfLife\Domain\Event\DomainEventInterface;
use PhpSpec\ObjectBehavior;

class DomainEventCollectionSpec extends ObjectBehavior
{
    function let(
        DomainEventInterface $event1,
        DomainEventInterface $event2
    ) {
        $this->beConstructedWith([$event1, $event2]);
    }

    function it_is_iterable(
        DomainEventInterface $event1,
        DomainEventInterface $event2
    ) {
        $this->shouldIterateAs([$event1, $event2]);
    }

    function it_is_countable()
    {
        $this->shouldHaveCount(2);
    }

    function it_is_accessible_as_an_array(
        DomainEventInterface $event1
    ) {
        $this[0]->shouldBe($event1);
    }

    function it_is_immutable(
        DomainEventInterface $event
    ) {
        $this->shouldThrow(CollectionIsImmutableException::class)->during('offsetSet', [0, $event]);
        $this->shouldThrow(CollectionIsImmutableException::class)->during('offsetUnset', [0]);
    }

    function it_stores_only_domain_events()
    {
        $this->beConstructedWith([new \stdClass()]);

        $this->shouldThrow(UnsupportedCollectionItemTypeException::class)->duringInstantiation();
    }
}

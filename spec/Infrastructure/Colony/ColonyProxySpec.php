<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Colony;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Infrastructure\Exception\CorruptedColonyException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ColonyProxySpec extends ObjectBehavior
{
    function let(
        ColonyRepositoryInterface $repository,
        ColonyId $id,
        ColonyInterface $colony
    ) {
        $repository->find($id)->willReturn($colony);

        $id->toString()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->beConstructedWith($repository, $id, 42, 16, 9);
    }

    function it_returns_the_proxy_colony_id(
        ColonyRepositoryInterface $repository,
        ColonyId $id
    ) {
        $repository->find(Argument::any())->shouldNotBeCalled();

        $this->getId()->shouldReturn($id);
    }

    function it_returns_the_proxy_generation(
        ColonyRepositoryInterface $repository
    ) {
        $repository->find(Argument::any())->shouldNotBeCalled();

        $this->getGeneration()->shouldReturn(42);
    }

    function it_returns_the_proxy_width(
        ColonyRepositoryInterface $repository
    ) {
        $repository->find(Argument::any())->shouldNotBeCalled();

        $this->getWidth()->shouldReturn(16);
    }

    function it_returns_the_proxy_height(
        ColonyRepositoryInterface $repository
    ) {
        $repository->find(Argument::any())->shouldNotBeCalled();

        $this->getHeight()->shouldReturn(9);
    }

    function it_loads_the_complete_colony_when_getting_cell_states(
        ColonyInterface $colony
    ) {
        $colony->getCellStates()->shouldBeCalled()->willReturn(["dead", "live", "live"]);

        $this->getCellStates()->shouldReturn(["dead", "live", "live"]);
    }

    function it_loads_the_complete_colony_when_applying_domain_events(
        ColonyInterface $colony,
        ColonyInterface $newColony,
        DomainEventInterface $event
    ) {
        $colony->apply([$event])->shouldBeCalled()->willReturn($newColony);

        $this->apply([$event])->shouldReturn($newColony);
    }

    function it_loads_the_complete_colony_when_evolving(
        ColonyInterface $colony,
        EvolveCellInterface $evolveCell,
        DomainEventInterface $event
    ) {
        $colony->evolve($evolveCell)->shouldBeCalled()->willReturn([$event]);

        $this->evolve($evolveCell)->shouldReturn([$event]);
    }

    function it_throws_an_exception_when_the_complete_colony_does_not_exists(
        ColonyRepositoryInterface $repository,
        ColonyId $id
    ) {
        $repository->find($id)->willReturn(null);

        $this->shouldThrow(CorruptedColonyException::class)->during('getCellStates', []);
    }
}

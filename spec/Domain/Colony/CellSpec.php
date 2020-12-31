<?php

declare(strict_types=1);

namespace spec\GameOfLife\Domain\Colony;

use GameOfLife\Domain\Colony\Cell;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use PhpSpec\ObjectBehavior;

class CellSpec extends ObjectBehavior
{
    function it_is_live_when_its_state_is_live()
    {
        $this->beConstructedWith('live');

        $this->isLive()->shouldReturn(true);

        $this->isInState('live')->shouldReturn(true);
        $this->isInState('dead')->shouldReturn(false);

        $this->getState()->shouldReturn('live');
    }

    function it_is_not_live_when_its_state_is_dead()
    {
        $this->beConstructedWith('dead');

        $this->isLive()->shouldReturn(false);

        $this->isInState('live')->shouldReturn(false);
        $this->isInState('dead')->shouldReturn(true);

        $this->getState()->shouldReturn('dead');
    }

    function it_throws_an_exception_when_it_is_constructed_with_an_invalid_state()
    {
        $this->beConstructedWith('cookie');

        $this->shouldThrow(InvalidCellStateException::class)->duringInstantiation();
    }

    function it_returns_a_new_cell_when_evolving()
    {
        $this->beConstructedWith('live');

        $this->evolveTo('dead')->shouldBeLike(new Cell('dead'));

        $this->getState()->shouldReturn('live');
    }

    function it_throws_an_exception_when_evolving_to_an_invalid_state()
    {
        $this->beConstructedWith('live');

        $this->shouldThrow(InvalidCellStateException::class)->during('evolveTo', ['cookie']);
    }
}

<?php

declare(strict_types=1);

namespace spec\GameOfLife\Domain\Colony;

use GameOfLife\Domain\Colony\CellInterface;
use PhpSpec\ObjectBehavior;

class EvolveCellSpec extends ObjectBehavior
{
    function let(
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $n1->isLive()->willReturn(false);
        $n2->isLive()->willReturn(false);
        $n3->isLive()->willReturn(false);
        $n4->isLive()->willReturn(false);
        $n5->isLive()->willReturn(false);
        $n6->isLive()->willReturn(false);
        $n7->isLive()->willReturn(false);
        $n8->isLive()->willReturn(false);
    }

    function it_evolves_a_dead_cell_to_a_live_one_when_it_has_exactly_three_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(false);

        $n3->isLive()->willReturn(true);
        $n4->isLive()->willReturn(true);
        $n6->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('live');
    }

    function it_lets_a_dead_cell_dead_when_it_has_less_than_three_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(false);

        $n3->isLive()->willReturn(true);
        $n6->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('dead');
    }

    function it_lets_a_dead_cell_dead_when_it_has_more_than_three_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(false);

        $n3->isLive()->willReturn(true);
        $n4->isLive()->willReturn(true);
        $n6->isLive()->willReturn(true);
        $n8->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('dead');
    }

    function it_evolves_a_live_cell_to_a_dead_one_when_it_has_less_than_two_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(true);

        $n3->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('dead');
    }

    function it_evolves_a_live_cell_to_a_dead_one_when_it_has_more_than_three_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(true);

        $n3->isLive()->willReturn(true);
        $n4->isLive()->willReturn(true);
        $n6->isLive()->willReturn(true);
        $n8->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('dead');
    }

    function it_lets_a_live_cell_live_when_it_has_two_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(true);

        $n1->isLive()->willReturn(true);
        $n3->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('live');
    }

    function it_lets_a_live_cell_live_when_it_has_three_live_neighbours(
        CellInterface $cell,
        CellInterface $n1,
        CellInterface $n2,
        CellInterface $n3,
        CellInterface $n4,
        CellInterface $n5,
        CellInterface $n6,
        CellInterface $n7,
        CellInterface $n8
    ) {
        $cell->isLive()->willReturn(true);

        $n1->isLive()->willReturn(true);
        $n3->isLive()->willReturn(true);
        $n7->isLive()->willReturn(true);

        $this->execute($cell, [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8])->shouldReturn('live');
    }
}

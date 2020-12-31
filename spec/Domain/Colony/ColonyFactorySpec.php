<?php

declare(strict_types=1);

namespace spec\GameOfLife\Domain\Colony;

use GameOfLife\Domain\Colony\Cell;
use GameOfLife\Domain\Colony\Colony;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Time\ClockInterface;
use PhpSpec\ObjectBehavior;

class ColonyFactorySpec extends ObjectBehavior
{
    function let(ClockInterface $clock)
    {
        $this->beConstructedWith($clock);
    }

    function it_creates_a_new_colony_at_generation_zero(
        ClockInterface $clock,
        ColonyId $id
    ) {
        $this
            ->create($id, 3, 1, ['live', 'dead', 'dead'])
            ->shouldBeLike(
                new Colony(
                    $clock->getWrappedObject(),
                    $id->getWrappedObject(),
                    0,
                    3,
                    1,
                    [
                        new Cell('live'),
                        new Cell('dead'),
                        new Cell('dead')
                    ]
                )
            );
    }

    function it_throws_an_exception_when_the_width_is_negative_or_nul(
        ColonyId $id
    ) {
        $this->shouldThrow(InvalidColonyDimensionException::class)->during('create', [$id, 0, 1, []]);
        $this->shouldThrow(InvalidColonyDimensionException::class)->during('create', [$id, -1, 1, []]);
    }

    function it_throws_an_exception_when_the_height_is_negative_or_nul(
        ColonyId $id
    ) {
        $this->shouldThrow(InvalidColonyDimensionException::class)->during('create', [$id, 3, 0, []]);
        $this->shouldThrow(InvalidColonyDimensionException::class)->during('create', [$id, 3, -1, []]);
    }

    function it_throws_an_exception_when_the_number_of_the_cells_does_not_match_the_width_and_height(
        ColonyId $id
    ) {
        $this->shouldThrow(InvalidColonyDimensionException::class)->during('create', [$id, 3, 1, ['live', 'dead']]);
    }
}

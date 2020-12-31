<?php

declare(strict_types=1);

namespace spec\GameOfLife\Domain\Colony;

use GameOfLife\Domain\Colony\CellInterface;
use GameOfLife\Domain\Colony\Colony;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Time\ClockInterface;
use PhpSpec\ObjectBehavior;

class ColonySpec extends ObjectBehavior
{
    function let(ColonyId $id)
    {
        $id->equals($id)->willReturn(true);
    }

    function it_gets_cell_states(
        ClockInterface $clock,
        ColonyId $id,
        CellInterface $cell0,
        CellInterface $cell1,
        CellInterface $cell2,
        CellInterface $cell3
    ) {
        $this->beConstructedWith($clock, $id, 42, 2, 2, [$cell0, $cell1, $cell2, $cell3]);

        $cell0->getState()->willReturn('dead');
        $cell1->getState()->willReturn('live');
        $cell2->getState()->willReturn('dead');
        $cell3->getState()->willReturn('dead');

        $this->getCellStates()->shouldReturn(['dead', 'live', 'dead', 'dead']);
    }

    function it_returns_all_the_domain_events_that_need_to_be_applied_to_evolve_to_the_next_generation(
        ClockInterface $clock,
        ColonyId $id,
        CellInterface $cell0,
        CellInterface $cell1,
        CellInterface $cell2,
        CellInterface $cell3,
        CellInterface $cell4,
        CellInterface $cell5,
        CellInterface $cell6,
        CellInterface $cell7,
        CellInterface $cell8,
        EvolveCellInterface $evolveCell
    ) {
        $cells = [$cell0, $cell1, $cell2, $cell3, $cell4, $cell5, $cell6, $cell7, $cell8];
        $this->beConstructedWith($clock, $id, 42, 3, 3, $cells);

        $now = new \DateTime();
        $clock->getCurrentDateTime()->willReturn($now);

        $evolveCell
            ->execute($cell0, [$cell1, $cell3, $cell4])
            ->shouldBeCalled()
            ->willReturn('dead');

        $cell0->isLive()->willReturn(false);
        $cell0->isInState('dead')->willReturn(true);

        $evolveCell
            ->execute($cell1, [$cell0, $cell2, $cell3, $cell4, $cell5])
            ->shouldBeCalled()
            ->willReturn('live');

        $cell1->isLive()->willReturn(true);
        $cell1->isInState('live')->willReturn(true);

        $evolveCell
            ->execute($cell2, [$cell1, $cell4, $cell5])
            ->shouldBeCalled()
            ->willReturn('live');

        $cell2->isLive()->willReturn(false);
        $cell2->isInState('live')->willReturn(false);

        $evolveCell
            ->execute($cell3, [$cell0, $cell1, $cell4, $cell6, $cell7])
            ->shouldBeCalled()
            ->willReturn('live');

        $cell3->isLive()->willReturn(false);
        $cell3->isInState('live')->willReturn(false);

        $evolveCell
            ->execute($cell4, [$cell0, $cell1, $cell2, $cell3, $cell5, $cell6, $cell7, $cell8])
            ->shouldBeCalled()
            ->willReturn('dead');

        $cell4->isLive()->willReturn(true);
        $cell4->isInState('dead')->willReturn(false);

        $evolveCell
            ->execute($cell5, [$cell1, $cell2, $cell4, $cell7, $cell8])
            ->shouldBeCalled()
            ->willReturn('live');

        $cell5->isLive()->willReturn(true);
        $cell5->isInState('live')->willReturn(true);

        $evolveCell
            ->execute($cell6, [$cell3, $cell4, $cell7])
            ->shouldBeCalled()
            ->willReturn('dead');

        $cell6->isLive()->willReturn(true);
        $cell6->isInState('dead')->willReturn(false);

        $evolveCell
            ->execute($cell7, [$cell3, $cell4, $cell5, $cell6, $cell8])
            ->shouldBeCalled()
            ->willReturn('dead');

        $cell7->isLive()->willReturn(false);
        $cell7->isInState('dead')->willReturn(true);

        $evolveCell
            ->execute($cell8, [$cell4, $cell5, $cell7])
            ->shouldBeCalled()
            ->willReturn('live');

        $cell8->isLive()->willReturn(true);
        $cell8->isInState('live')->willReturn(true);

        $this
            ->evolve($evolveCell)
            ->shouldBeLike(
                [
                    new CellBorn($id->getWrappedObject(), $now, 2),
                    new CellBorn($id->getWrappedObject(), $now, 3),
                    new CellDied($id->getWrappedObject(), $now, 4),
                    new CellDied($id->getWrappedObject(), $now, 6),
                    new GenerationEnded($id->getWrappedObject(), $now, 42),
                ]
            );
    }

    function it_creates_an_new_colony_by_applying_domain_events(
        ClockInterface $clock,
        ColonyId $id,
        CellInterface $cell0,
        CellInterface $cell1,
        CellInterface $cell2,
        CellInterface $cell3,
        CellInterface $cell4,
        CellInterface $cell5,
        CellInterface $cell6,
        CellInterface $cell7,
        CellInterface $cell8,
        CellInterface $newCell2,
        CellInterface $newCell3,
        CellInterface $newCell4,
        CellInterface $newCell6,
        CellBorn $cell2Born,
        CellBorn $cell3Born,
        CellDied $cell4Died,
        CellDied $cell6Died,
        GenerationEnded $generation42Ended
    ) {
        $cells = [$cell0, $cell1, $cell2, $cell3, $cell4, $cell5, $cell6, $cell7, $cell8];
        $this->beConstructedWith($clock, $id, 42, 3, 3, $cells);

        $cell2Born->getEntityId()->willReturn($id);
        $cell2Born->getIndex()->willReturn(2);

        $cell3Born->getEntityId()->willReturn($id);
        $cell3Born->getIndex()->willReturn(3);

        $cell4Died->getEntityId()->willReturn($id);
        $cell4Died->getIndex()->willReturn(4);

        $cell6Died->getEntityId()->willReturn($id);
        $cell6Died->getIndex()->willReturn(6);

        $generation42Ended->getEntityId()->willReturn($id);
        $generation42Ended->getGeneration()->willReturn(42);

        $cell2->evolveTo('live')->willReturn($newCell2);
        $cell3->evolveTo('live')->willReturn($newCell3);
        $cell4->evolveTo('dead')->willReturn($newCell4);
        $cell6->evolveTo('dead')->willReturn($newCell6);

        $this
            ->apply([$cell2Born, $cell3Born, $cell4Died, $cell6Died, $generation42Ended])
            ->shouldBeLike(
                new Colony(
                    $clock->getWrappedObject(),
                    $id->getWrappedObject(),
                    43,
                    3,
                    3,
                    [
                        $cell0->getWrappedObject(),
                        $cell1->getWrappedObject(),
                        $newCell2->getWrappedObject(),
                        $newCell3->getWrappedObject(),
                        $newCell4->getWrappedObject(),
                        $cell5->getWrappedObject(),
                        $newCell6->getWrappedObject(),
                        $cell7->getWrappedObject(),
                        $cell8->getWrappedObject(),
                    ]
                )
            );
    }

    function it_drops_domain_events_referencing_another_colony(
        ClockInterface $clock,
        ColonyId $id,
        ColonyId $anotherId,
        CellInterface $cell,
        CellDied $cellDied
    ) {
        $this->beConstructedWith($clock, $id, 42, 1, 1, [$cell]);

        $id->equals($anotherId)->willReturn(false);

        $cellDied->getEntityId()->willReturn($anotherId);
        $cellDied->getIndex()->willReturn(0);

        $this
            ->apply([$cellDied])
            ->shouldBeLike(
                new Colony($clock->getWrappedObject(), $id->getWrappedObject(), 42, 1, 1, [$cell->getWrappedObject()])
            );
    }

    function it_drops_generation_ended_events_when_the_generation_does_not_match(
        ClockInterface $clock,
        ColonyId $id,
        CellInterface $cell,
        GenerationEnded $generation42Ended,
        GenerationEnded $generation54Ended,
        GenerationEnded $generation43Ended,
        GenerationEnded $generation34Ended
    ) {
        $this->beConstructedWith($clock, $id, 42, 1, 1, [$cell]);

        $generation42Ended->getEntityId()->willReturn($id);
        $generation42Ended->getGeneration()->willReturn(42);

        $generation54Ended->getEntityId()->willReturn($id);
        $generation54Ended->getGeneration()->willReturn(54);

        $generation43Ended->getEntityId()->willReturn($id);
        $generation43Ended->getGeneration()->willReturn(43);
        
        $generation34Ended->getEntityId()->willReturn($id);
        $generation34Ended->getGeneration()->willReturn(34);

        $this
            ->apply([$generation42Ended, $generation54Ended, $generation43Ended, $generation34Ended])
            ->shouldBeLike(
                new Colony($clock->getWrappedObject(), $id->getWrappedObject(), 44, 1, 1, [$cell->getWrappedObject()])
            );
    }
}

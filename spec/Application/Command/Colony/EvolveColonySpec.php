<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\EvolveColonyCommand;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use PhpSpec\ObjectBehavior;

class EvolveColonySpec extends ObjectBehavior
{
    function let(
        ColonyRepositoryInterface $repository,
        EvolveCellInterface $evolveCell
    ) {
        $this->beConstructedWith($repository, $evolveCell);
    }

    function it_responds_to_its_command()
    {
        $this->respondTo()->shouldReturn(EvolveColonyCommand::class);
    }

    function it_throws_an_exception_when_the_command_does_not_correspond_to_the_type_it_responds_to(
        CommandInterface $query
    ) {
        $this->shouldThrow(InvalidRequestTypeException::class)->during('execute', [$query]);
    }

    function it_evolves_the_requested_colony(
        ColonyRepositoryInterface $repository,
        EvolveCellInterface $evolveCell,
        EvolveColonyCommand $command,
        ColonyInterface $colony,
        ColonyId $colonyId,
        DomainEventInterface $event
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->find($colonyId)->willReturn($colony);

        $colony->evolve($evolveCell)->willReturn([$event]);

        $repository->commit([$event])->shouldBeCalled();

        $this->execute($command)->shouldBeLike(new DomainEventCollection([$event->getWrappedObject()]));
    }

    function it_throws_an_exception_when_the_colony_cannot_be_found(
        ColonyRepositoryInterface $repository,
        EvolveColonyCommand $command,
        ColonyId $colonyId
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->find($colonyId)->willReturn(null);

        $this->shouldThrow(ColonyNotFoundException::class)->during('execute', [$command]);
    }

    function it_throws_an_exception_when_the_repository_is_not_available(
        ColonyRepositoryInterface $repository,
        EvolveColonyCommand $command
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository
            ->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')
            ->willThrow(new RepositoryNotAvailableException('Oops'));

        $this->shouldThrow(TechnicalException::class)->during('execute', [$command]);
    }
}

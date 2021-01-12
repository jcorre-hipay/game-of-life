<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\DeleteColonyCommand;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use PhpSpec\ObjectBehavior;

class DeleteColonySpec extends ObjectBehavior
{
    function let(ColonyRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_responds_to_its_command()
    {
        $this->respondTo()->shouldReturn(DeleteColonyCommand::class);
    }

    function it_throws_an_exception_when_the_command_does_not_correspond_to_the_type_it_responds_to(
        CommandInterface $query
    ) {
        $this->shouldThrow(InvalidRequestTypeException::class)->during('execute', [$query]);
    }

    function it_removes_a_colony_from_the_repository(
        ColonyRepositoryInterface $repository,
        DeleteColonyCommand $command,
        ColonyId $colonyId,
        ColonyDestroyed $colonyDestroyed
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->remove($colonyId)->shouldBeCalled()->willReturn($colonyDestroyed);

        $this->execute($command)->shouldBeLike(new DomainEventCollection([$colonyDestroyed->getWrappedObject()]));
    }

    function it_throws_an_exception_when_the_colony_cannot_be_found(
        ColonyRepositoryInterface $repository,
        DeleteColonyCommand $command,
        ColonyId $colonyId
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->remove($colonyId)->willThrow(new ColonyDoesNotExistException('Oops'));

        $this->shouldThrow(ColonyNotFoundException::class)->during('execute', [$command]);
    }

    function it_throws_an_exception_when_the_repository_is_not_available(
        ColonyRepositoryInterface $repository,
        DeleteColonyCommand $command
    ) {
        $command->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $repository
            ->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')
            ->willThrow(new RepositoryNotAvailableException('Oops'));

        $this->shouldThrow(TechnicalException::class)->during('execute', [$command]);
    }
}

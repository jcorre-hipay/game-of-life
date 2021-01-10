<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\CreateColonyCommand;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateColonySpec extends ObjectBehavior
{
    function let(
        ColonyRepositoryInterface $repository,
        ColonyFactoryInterface $factory
    ) {
        $this->beConstructedWith($repository, $factory);
    }

    function it_responds_to_its_command()
    {
        $this->respondTo()->shouldReturn(CreateColonyCommand::class);
    }

    function it_throws_an_exception_when_the_command_does_not_correspond_to_the_type_it_responds_to(
        CommandInterface $query
    ) {
        $this->shouldThrow(InvalidRequestTypeException::class)->during('execute', [$query]);
    }

    function it_creates_and_stores_a_new_colony(
        ColonyRepositoryInterface $repository,
        ColonyFactoryInterface $factory,
        CreateColonyCommand $command,
        ColonyInterface $colony,
        ColonyId $colonyId,
        ColonyCreated $colonyCreated
    ) {
        $command->getWidth()->willReturn(3);
        $command->getHeight()->willReturn(2);
        $command->getCellStates()->willReturn(['dead', 'live', 'dead', 'live', 'dead', 'live']);

        $repository->nextId()->willReturn($colonyId);
        $factory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])->willReturn($colony);
        $repository->add($colony)->shouldBeCalled()->willReturn($colonyCreated);

        $this->execute($command)->shouldBeLike(new DomainEventCollection([$colonyCreated->getWrappedObject()]));
    }

    function it_throws_an_exception_when_the_colony_cannot_be_created_due_to_an_invalid_cell_state(
        ColonyRepositoryInterface $repository,
        ColonyFactoryInterface $factory,
        CreateColonyCommand $command,
        ColonyId $colonyId
    ) {
        $command->getWidth()->willReturn(1);
        $command->getHeight()->willReturn(1);
        $command->getCellStates()->willReturn(['undead']);

        $repository->nextId()->willReturn($colonyId);

        $factory
            ->create(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willThrow(new InvalidCellStateException());

        $this->shouldThrow(InvalidParametersException::class)->during('execute', [$command]);
    }

    function it_throws_an_exception_when_the_colony_cannot_be_created_due_to_an_invalid_cell_count(
        ColonyRepositoryInterface $repository,
        ColonyFactoryInterface $factory,
        CreateColonyCommand $command,
        ColonyId $colonyId
    ) {
        $command->getWidth()->willReturn(2);
        $command->getHeight()->willReturn(1);
        $command->getCellStates()->willReturn(['dead']);

        $repository->nextId()->willReturn($colonyId);

        $factory
            ->create(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willThrow(new InvalidColonyDimensionException());

        $this->shouldThrow(InvalidParametersException::class)->during('execute', [$command]);
    }

    function it_throws_an_exception_when_the_repository_is_not_available(
        ColonyRepositoryInterface $repository,
        CreateColonyCommand $command
    ) {
        $repository->nextId()->willThrow(new RepositoryNotAvailableException('Oops'));

        $this->shouldThrow(TechnicalException::class)->during('execute', [$command]);
    }

    function it_throws_an_exception_when_the_colony_cannot_be_created_because_it_already_exists(
        ColonyRepositoryInterface $repository,
        ColonyFactoryInterface $factory,
        CreateColonyCommand $command,
        ColonyInterface $colony,
        ColonyId $colonyId
    ) {
        $command->getWidth()->willReturn(3);
        $command->getHeight()->willReturn(2);
        $command->getCellStates()->willReturn(['dead', 'live', 'dead', 'live', 'dead', 'live']);

        $repository->nextId()->willReturn($colonyId);
        $factory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])->willReturn($colony);
        $repository->add($colony)->willThrow(new ColonyAlreadyExistsException());

        $this->shouldThrow(TechnicalException::class)->during('execute', [$command]);
    }
}

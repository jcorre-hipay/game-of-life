<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\CommandHandlerInterface;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Command\DomainEventCollectionInterface;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\InvalidColonyDimensionException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;

class CreateColony implements CommandHandlerInterface
{
    private $repository;
    private $factory;

    /**
     * @param ColonyRepositoryInterface $repository
     * @param ColonyFactoryInterface $factory
     */
    public function __construct(ColonyRepositoryInterface $repository, ColonyFactoryInterface $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function respondTo(): string
    {
        return CreateColonyCommand::class;
    }

    /**
     * @param CommandInterface $command
     * @return DomainEventCollectionInterface
     * @throws ApplicationException
     */
    public function execute(CommandInterface $command): DomainEventCollectionInterface
    {
        if (!$command instanceof CreateColonyCommand) {
            throw new InvalidRequestTypeException(\sprintf('Cannot handle command of type %s.', \get_class($command)));
        }

        try {
            $colony = $this->factory->create(
                $this->repository->nextId(),
                $command->getWidth(),
                $command->getHeight(),
                $command->getCellStates()
            );

            $event = $this->repository->add($colony);

            return new DomainEventCollection([$event]);
        } catch (InvalidCellStateException $exception) {
            throw new InvalidParametersException(['Invalid cell state.'], 'Invalid parameters.', 0, $exception);
        } catch (InvalidColonyDimensionException $exception) {
            throw new InvalidParametersException(['Invalid colony dimensions.'], 'Invalid parameters.', 0, $exception);
        } catch (RepositoryNotAvailableException $exception) {
            throw new TechnicalException('Colony repository is not available.', 0, $exception);
        } catch (ColonyAlreadyExistsException $exception) {
            throw new TechnicalException('Trying to add a colony that already exists.', 0, $exception);
        }
    }
}

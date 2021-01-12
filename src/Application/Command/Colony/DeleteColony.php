<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\CommandHandlerInterface;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Command\DomainEventCollectionInterface;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;

class DeleteColony implements CommandHandlerInterface
{
    private $repository;

    /**
     * @param ColonyRepositoryInterface $repository
     */
    public function __construct(ColonyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function respondTo(): string
    {
        return DeleteColonyCommand::class;
    }

    /**
     * @param CommandInterface $command
     * @return DomainEventCollectionInterface
     * @throws ApplicationException
     */
    public function execute(CommandInterface $command): DomainEventCollectionInterface
    {
        if (!$command instanceof DeleteColonyCommand) {
            throw new InvalidRequestTypeException(\sprintf('Cannot handle command of type %s.', \get_class($command)));
        }

        try {
            $event = $this->repository->remove($this->repository->getIdFromString($command->getColonyId()));

            return new DomainEventCollection([$event]);
        } catch (ColonyDoesNotExistException $exception) {
            throw new ColonyNotFoundException(\sprintf('Cannot find colony %s.', $command->getColonyId()), 0, $exception);
        } catch (RepositoryNotAvailableException $exception) {
            throw new TechnicalException('Colony repository is not available.', 0, $exception);
        }
    }
}

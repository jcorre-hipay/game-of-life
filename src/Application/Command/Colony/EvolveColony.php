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
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;

class EvolveColony implements CommandHandlerInterface
{
    private $repository;
    private $evolveCell;

    /**
     * @param ColonyRepositoryInterface $repository
     * @param EvolveCellInterface $evolveCell
     */
    public function __construct(ColonyRepositoryInterface $repository, EvolveCellInterface $evolveCell)
    {
        $this->repository = $repository;
        $this->evolveCell = $evolveCell;
    }

    /**
     * @return string
     */
    public function respondTo(): string
    {
        return EvolveColonyCommand::class;
    }

    /**
     * @param CommandInterface $command
     * @return DomainEventCollectionInterface
     * @throws ApplicationException
     */
    public function execute(CommandInterface $command): DomainEventCollectionInterface
    {
        if (!$command instanceof EvolveColonyCommand) {
            throw new InvalidRequestTypeException(\sprintf('Cannot handle command of type %s.', \get_class($command)));
        }

        try {
            $colony = $this->repository->find($this->repository->getIdFromString($command->getColonyId()));

            if (!$colony instanceof ColonyInterface) {
                throw new ColonyNotFoundException(\sprintf('Cannot find colony %s.', $command->getColonyId()));
            }

            $events = $colony->evolve($this->evolveCell);

            $this->repository->commit($events);

            return new DomainEventCollection($events);
        } catch (RepositoryNotAvailableException $exception) {
            throw new TechnicalException('Colony repository is not available.', 0, $exception);
        }
    }
}

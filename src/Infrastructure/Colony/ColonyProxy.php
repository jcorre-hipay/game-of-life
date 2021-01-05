<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Colony;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Domain\Exception\InvalidCellStateException;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use GameOfLife\Infrastructure\Exception\CorruptedColonyException;

class ColonyProxy implements ColonyInterface
{
    private $repository;
    private $id;
    private $generation;
    private $width;
    private $height;
    private $colony;

    /**
     * @param ColonyRepositoryInterface $repository
     * @param ColonyId $id
     * @param int $generation
     * @param int $width
     * @param int $height
     */
    public function __construct(
        ColonyRepositoryInterface $repository,
        ColonyId $id,
        int $generation,
        int $width,
        int $height
    ) {
        $this->repository = $repository;
        $this->id = $id;
        $this->generation = $generation;
        $this->width = $width;
        $this->height = $height;
        $this->colony = null;
    }

    /**
     * @return ColonyId
     */
    public function getId(): ColonyId
    {
        return $this->id;
    }

    /**
     * @param DomainEventInterface[] $events
     * @return ColonyInterface
     * @throws InvalidCellStateException
     * @throws CorruptedColonyException
     * @throws RepositoryNotAvailableException
     */
    public function apply(array $events): ColonyInterface
    {
        return $this->getColony()->apply($events);
    }

    /**
     * @param EvolveCellInterface $evolveCell
     * @return DomainEventInterface[]
     * @throws CorruptedColonyException
     * @throws RepositoryNotAvailableException
     */
    public function evolve(EvolveCellInterface $evolveCell): array
    {
        return $this->getColony()->evolve($evolveCell);
    }

    /**
     * @return int
     */
    public function getGeneration(): int
    {
        return $this->generation;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return string[]
     * @throws CorruptedColonyException
     * @throws RepositoryNotAvailableException
     */
    public function getCellStates(): array
    {
        return $this->getColony()->getCellStates();
    }

    /**
     * @return ColonyInterface
     * @throws CorruptedColonyException
     * @throws RepositoryNotAvailableException
     */
    private function getColony(): ColonyInterface
    {
        if (!$this->colony instanceof ColonyInterface) {
            $this->colony = $this->loadColony();
        }

        return $this->colony;
    }

    /**
     * @return ColonyInterface
     * @throws CorruptedColonyException
     * @throws RepositoryNotAvailableException
     */
    private function loadColony(): ColonyInterface
    {
        $colony = $this->repository->find($this->id);

        if (!$colony instanceof ColonyInterface) {
            throw new CorruptedColonyException(
                \sprintf('Colony %s is referenced but does not exist.', $this->id->toString())
            );
        }

        return $colony;
    }
}

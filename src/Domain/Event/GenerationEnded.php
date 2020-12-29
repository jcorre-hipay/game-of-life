<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;

class GenerationEnded implements DomainEventInterface
{
    private $colonyId;
    private $eventDate;
    private $generation;

    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $eventDate
     * @param int $generation
     */
    public function __construct(ColonyId $colonyId, \DateTimeInterface $eventDate, int $generation)
    {
        $this->colonyId = $colonyId;
        $this->eventDate = $eventDate;
        $this->generation = $generation;
    }

    /**
     * @return EntityIdInterface
     */
    public function getEntityId(): EntityIdInterface
    {
        return $this->colonyId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEventDate(): \DateTimeInterface
    {
        return $this->eventDate;
    }

    /**
     * @return int
     */
    public function getGeneration(): int
    {
        return $this->generation;
    }
}

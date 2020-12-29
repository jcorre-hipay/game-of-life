<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;

class ColonyCreated implements DomainEventInterface
{
    private $colonyId;
    private $eventDate;
    private $cellStates;

    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $eventDate
     * @param string[] $cellStates
     */
    public function __construct(ColonyId $colonyId, \DateTimeInterface $eventDate, array $cellStates)
    {
        $this->colonyId = $colonyId;
        $this->eventDate = $eventDate;
        $this->cellStates = $cellStates;
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
     * @return string[]
     */
    public function getCellStates(): array
    {
        return $this->cellStates;
    }
}

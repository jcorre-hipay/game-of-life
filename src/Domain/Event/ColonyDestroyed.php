<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;

class ColonyDestroyed implements DomainEventInterface
{
    private $colonyId;
    private $eventDate;

    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $eventDate
     */
    public function __construct(ColonyId $colonyId, \DateTimeInterface $eventDate)
    {
        $this->colonyId = $colonyId;
        $this->eventDate = $eventDate;
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
}

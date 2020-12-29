<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;

class CellDied implements DomainEventInterface
{
    private $colonyId;
    private $eventDate;
    private $index;

    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $eventDate
     * @param int $index
     */
    public function __construct(ColonyId $colonyId, \DateTimeInterface $eventDate, int $index)
    {
        $this->colonyId = $colonyId;
        $this->eventDate = $eventDate;
        $this->index = $index;
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
    public function getIndex(): int
    {
        return $this->index;
    }
}

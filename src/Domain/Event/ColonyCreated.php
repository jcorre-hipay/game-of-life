<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;

class ColonyCreated implements DomainEventInterface
{
    private $colonyId;
    private $eventDate;
    private $generation;
    private $width;
    private $height;
    private $cellStates;

    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $eventDate
     * @param int $generation
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     */
    public function __construct(
        ColonyId $colonyId,
        \DateTimeInterface $eventDate,
        int $generation,
        int $width,
        int $height,
        array $cellStates
    ) {
        $this->colonyId = $colonyId;
        $this->eventDate = $eventDate;
        $this->generation = $generation;
        $this->width = $width;
        $this->height = $height;
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
     */
    public function getCellStates(): array
    {
        return $this->cellStates;
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Event;

use GameOfLife\Domain\Core\EntityIdInterface;

interface DomainEventInterface
{
    /**
     * @return EntityIdInterface
     */
    public function getEntityId(): EntityIdInterface;

    /**
     * @return \DateTimeInterface
     */
    public function getEventDate(): \DateTimeInterface;
}

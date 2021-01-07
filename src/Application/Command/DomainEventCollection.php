<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command;

use GameOfLife\Application\AbstractCollection;
use GameOfLife\Domain\Event\DomainEventInterface;

class DomainEventCollection extends AbstractCollection implements DomainEventCollectionInterface
{
    /**
     * @param mixed $item
     * @return bool
     */
    protected function supports($item): bool
    {
        return $item instanceof DomainEventInterface;
    }
}

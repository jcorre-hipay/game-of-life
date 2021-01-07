<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\HandlerInterface;

interface CommandHandlerInterface extends HandlerInterface
{
    /**
     * @param CommandInterface $command
     * @return DomainEventCollectionInterface
     * @throws ApplicationException
     */
    public function execute(CommandInterface $command): DomainEventCollectionInterface;

}

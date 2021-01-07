<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollectionInterface;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\ResponseInterface;

interface CommandBusInterface
{
    /**
     * @param CommandInterface $command
     * @return DomainEventCollectionInterface
     * @throws ApplicationException
     */
    public function send(CommandInterface $command): ResponseInterface;
}

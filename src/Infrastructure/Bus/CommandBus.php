<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\ResponseInterface;

class CommandBus implements CommandBusInterface
{
    private $bus;

    /**
     * @param ApplicationBusInterface $bus
     */
    public function __construct(ApplicationBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param CommandInterface $command
     * @return ResponseInterface
     * @throws ApplicationException
     */
    public function send(CommandInterface $command): ResponseInterface
    {
        return $this->bus->send($command);
    }
}

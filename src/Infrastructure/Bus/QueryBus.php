<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\ResponseInterface;

class QueryBus implements QueryBusInterface
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
     * @param QueryInterface $query
     * @return ResponseInterface
     * @throws ApplicationException
     */
    public function send(QueryInterface $query): ResponseInterface
    {
        return $this->bus->send($query);
    }
}

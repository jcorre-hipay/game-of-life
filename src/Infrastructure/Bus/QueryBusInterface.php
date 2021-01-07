<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\Query\ResultInterface;
use GameOfLife\Application\ResponseInterface;

interface QueryBusInterface
{
    /**
     * @param QueryInterface $query
     * @return ResultInterface
     * @throws ApplicationException
     */
    public function send(QueryInterface $query): ResponseInterface;
}

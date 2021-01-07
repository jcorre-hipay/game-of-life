<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\HandlerInterface;

interface QueryHandlerInterface extends HandlerInterface
{
    /**
     * @param QueryInterface $query
     * @return ResultInterface
     * @throws ApplicationException
     */
    public function execute(QueryInterface $query): ResultInterface;
}

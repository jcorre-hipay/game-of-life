<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Database;

use GameOfLife\Infrastructure\Exception\DataAccessException;

interface ConnectionInterface
{
    /**
     * @param string $command
     * @param array $parameters
     * @throws DataAccessException
     */
    public function execute(string $command, array $parameters = []): void;

    /**
     * @param string $query
     * @param array $parameters
     * @return array
     * @throws DataAccessException
     */
    public function query(string $query, array $parameters = []): array;
}

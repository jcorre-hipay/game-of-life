<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Database;

use GameOfLife\Infrastructure\Exception\DataAccessException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;

class PdoConnection implements ConnectionInterface
{
    private $logger;
    private $databaseUrl;
    private $connection;

    /**
     * @param LoggerInterface $logger
     * @param string $databaseUrl
     */
    public function __construct(LoggerInterface $logger, string $databaseUrl)
    {
        $this->logger = $logger;
        $this->databaseUrl = $databaseUrl;
        $this->connection = null;
    }

    /**
     * @param string $command
     * @param array $parameters
     * @throws DataAccessException
     */
    public function execute(string $command, array $parameters = []): void
    {
        try {
            $this->logger->info(
                'Executing database command.',
                [
                    'command' => $command,
                    'parameters' => $parameters,
                ]
            );

            $this->getConnection()->prepare($command)->execute($parameters);
        } catch (\PDOException $exception) {
            throw new DataAccessException('Fail to execute the database command.', 0, $exception);
        }
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return array
     * @throws DataAccessException
     */
    public function query(string $query, array $parameters = []): array
    {
        try {
            $this->logger->info(
                'Executing database query.',
                [
                    'query' => $query,
                    'parameters' => $parameters,
                ]
            );

            $statement = $this->getConnection()->prepare($query);
            $statement->execute($parameters);

            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $exception) {
            throw new DataAccessException('Fail to execute the database query.', 0, $exception);
        }
    }

    /**
     * @return \PDO
     */
    private function getConnection(): \PDO
    {
        if (!$this->connection instanceof \PDO) {
            $this->logger->info('Creating a PDO object.');
            $this->connection = new \PDO($this->databaseUrl);
        }

        return $this->connection;
    }
}

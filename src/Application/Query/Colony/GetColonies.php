<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Application\Query\QueryHandlerInterface;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\Query\ResultInterface;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;

class GetColonies implements QueryHandlerInterface
{
    private $repository;

    /**
     * @param ColonyRepositoryInterface $repository
     */
    public function __construct(ColonyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function respondTo(): string
    {
        return GetColoniesQuery::class;
    }

    /**
     * @param QueryInterface $query
     * @return ResultInterface
     * @throws ApplicationException
     */
    public function execute(QueryInterface $query): ResultInterface
    {
        if (!$query instanceof GetColoniesQuery) {
            throw new InvalidRequestTypeException(\sprintf('Cannot handle query of type %s.', \get_class($query)));
        }

        try {
            return new ColonyResult(
                \array_map(
                    function (ColonyInterface $colony): Colony {
                        return new Colony($colony);
                    },
                    $this->repository->findAll()
                )
            );
        } catch (RepositoryNotAvailableException $exception) {
            throw new TechnicalException('Colony repository is not available.', 0, $exception);
        }
    }
}

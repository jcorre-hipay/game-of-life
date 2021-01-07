<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Query\Colony;

use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColonyQuery;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use PhpSpec\ObjectBehavior;

class GetColonySpec extends ObjectBehavior
{
    function let(ColonyRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_responds_to_its_query()
    {
        $this->respondTo()->shouldReturn(GetColonyQuery::class);
    }

    function it_throws_an_exception_when_the_query_does_not_correspond_to_the_type_it_responds_to(
        QueryInterface $query
    ) {
        $this->shouldThrow(InvalidRequestTypeException::class)->during('execute', [$query]);
    }

    function it_returns_the_requested_colony(
        ColonyRepositoryInterface $repository,
        GetColonyQuery $query,
        ColonyInterface $colony,
        ColonyId $colonyId
    ) {
        $query->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');
        $query->getGeneration()->willReturn(42);

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->find($colonyId, 42)->willReturn($colony);

        $this->execute($query)->shouldBeLike(new ColonyResult([new Colony($colony->getWrappedObject())]));
    }

    function it_returns_an_empty_result_when_the_colony_cannot_be_found(
        ColonyRepositoryInterface $repository,
        GetColonyQuery $query,
        ColonyId $colonyId
    ) {
        $query->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');
        $query->getGeneration()->willReturn(42);

        $repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')->willReturn($colonyId);
        $repository->find($colonyId, 42)->willReturn(null);

        $this->execute($query)->shouldBeLike(new ColonyResult([]));
    }

    function it_throws_an_exception_when_the_repository_is_not_available(
        ColonyRepositoryInterface $repository,
        GetColonyQuery $query
    ) {
        $query->getColonyId()->willReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');
        $query->getGeneration()->willReturn(42);

        $repository
            ->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162')
            ->willThrow(new RepositoryNotAvailableException('Oops'));

        $this->shouldThrow(TechnicalException::class)->during('execute', [$query]);
    }
}

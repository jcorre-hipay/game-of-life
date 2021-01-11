<?php

declare(strict_types=1);

namespace spec\GameOfLife\Application\Query\Colony;

use GameOfLife\Application\Exception\InvalidRequestTypeException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColoniesQuery;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Exception\RepositoryNotAvailableException;
use PhpSpec\ObjectBehavior;

class GetColoniesSpec extends ObjectBehavior
{
    function let(ColonyRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_responds_to_its_query()
    {
        $this->respondTo()->shouldReturn(GetColoniesQuery::class);
    }

    function it_throws_an_exception_when_the_query_does_not_correspond_to_the_type_it_responds_to(
        QueryInterface $query
    ) {
        $this->shouldThrow(InvalidRequestTypeException::class)->during('execute', [$query]);
    }

    function it_returns_an_empty_result_when_there_are_no_colonies(
        ColonyRepositoryInterface $repository,
        GetColoniesQuery $query
    ) {
        $repository->findAll()->willReturn([]);

        $this->execute($query)->shouldBeLike(new ColonyResult([]));
    }

    function it_returns_all_the_colonies(
        ColonyRepositoryInterface $repository,
        GetColoniesQuery $query,
        ColonyInterface $colony
    ) {
        $repository->findAll()->willReturn([$colony]);
        $colony->getGeneration()->willReturn(51);

        $this->execute($query)->shouldBeLike(new ColonyResult([new Colony($colony->getWrappedObject(), 51)]));
    }

    function it_throws_an_exception_when_the_repository_is_not_available(
        ColonyRepositoryInterface $repository,
        GetColoniesQuery $query
    ) {
        $repository->findAll()->willThrow(new RepositoryNotAvailableException('Oops'));

        $this->shouldThrow(TechnicalException::class)->during('execute', [$query]);
    }
}

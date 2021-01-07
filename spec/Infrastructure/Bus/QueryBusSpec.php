<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Bus\ApplicationBusInterface;
use PhpSpec\ObjectBehavior;

class QueryBusSpec extends ObjectBehavior
{
    function it_sends_the_query_to_the_application_bus(
        ApplicationBusInterface $bus,
        QueryInterface $query,
        ResponseInterface $response
    ) {
        $this->beConstructedWith($bus);

        $bus->send($query)->shouldBeCalled()->willReturn($response);

        $this->send($query)->shouldReturn($response);
    }
}

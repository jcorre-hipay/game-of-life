<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Command\CommandHandlerInterface;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Command\DomainEventCollectionInterface;
use GameOfLife\Application\HandlerInterface;
use GameOfLife\Application\Query\QueryHandlerInterface;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\Query\ResultInterface;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Infrastructure\Exception\HandlerNotFoundException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use PhpSpec\ObjectBehavior;

class DispatcherBusSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    function it_dispatches_the_command_to_the_corresponding_handler(
        CommandHandlerInterface $handler,
        CommandInterface $command,
        DomainEventCollectionInterface $response
    ) {
        $handler->respondTo()->willReturn(\get_class($command->getWrappedObject()));
        $handler->execute($command)->shouldBeCalled()->willReturn($response);

        $this->register($handler);
        $this->send($command)->shouldReturn($response);
    }

    function it_dispatches_the_query_to_the_corresponding_handler(
        QueryHandlerInterface $handler,
        QueryInterface $query,
        ResultInterface $response
    ) {
        $handler->respondTo()->willReturn(\get_class($query->getWrappedObject()));
        $handler->execute($query)->shouldBeCalled()->willReturn($response);

        $this->register($handler);
        $this->send($query)->shouldReturn($response);
    }

    function it_throws_an_exception_when_there_are_no_corresponding_handler_for_the_command(
        CommandInterface $command
    ) {
        $this->shouldThrow(HandlerNotFoundException::class)->during('send', [$command]);
    }

    function it_throws_an_exception_when_there_are_no_corresponding_handler_for_the_query(
        QueryInterface $query
    ) {
        $this->shouldThrow(HandlerNotFoundException::class)->during('send', [$query]);
    }

    function it_throws_an_exception_when_the_request_type_is_unknown(
        HandlerInterface $handler,
        RequestInterface $request
    ) {
        $handler->respondTo()->willReturn(\get_class($request->getWrappedObject()));

        $this->register($handler);
        $this->shouldThrow(HandlerNotFoundException::class)->during('send', [$request]);
    }

    function it_throws_an_exception_when_a_request_type_does_not_match_its_handler_type(
        CommandHandlerInterface $commandHandler,
        CommandInterface $command,
        QueryHandlerInterface $queryHandler,
        QueryInterface $query
    ) {
        $commandHandler->respondTo()->willReturn(\get_class($query->getWrappedObject()));
        $queryHandler->respondTo()->willReturn(\get_class($command->getWrappedObject()));

        $this->register($commandHandler);
        $this->register($queryHandler);

        $this->shouldThrow(HandlerNotFoundException::class)->during('send', [$command]);
        $this->shouldThrow(HandlerNotFoundException::class)->during('send', [$query]);
    }
}

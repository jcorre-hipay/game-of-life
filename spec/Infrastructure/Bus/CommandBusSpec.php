<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Bus\ApplicationBusInterface;
use PhpSpec\ObjectBehavior;

class CommandBusSpec extends ObjectBehavior
{
    function it_sends_the_command_to_the_application_bus(
        ApplicationBusInterface $bus,
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $this->beConstructedWith($bus);

        $bus->send($command)->shouldBeCalled()->willReturn($response);

        $this->send($command)->shouldReturn($response);
    }
}

<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Bus\ApplicationBusInterface;
use GameOfLife\Infrastructure\Exception\ValidationFailedException;
use GameOfLife\Infrastructure\Validator\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidatorBusSpec extends ObjectBehavior
{
    function let(
        ApplicationBusInterface $next,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($next, $validator);
    }

    function it_validates_the_request_before_sending_it_to_the_next_bus(
        ApplicationBusInterface $next,
        ValidatorInterface $validator,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $validator->validate($request)->shouldBeCalled();

        $next->send($request)->shouldBeCalled()->willReturn($response);

        $this->send($request)->shouldReturn($response);
    }

    function it_throws_an_application_exception_when_the_validation_fails(
        ApplicationBusInterface $next,
        ValidatorInterface $validator,
        RequestInterface $request
    ) {
        $validator
            ->validate($request)
            ->willThrow(new ValidationFailedException(['Id must be a UUID.', 'Width must be positive.']));

        $next->send(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new InvalidParametersException(['Id must be a UUID.', 'Width must be positive.']))
            ->during('send', [$request]);
    }

    function it_does_not_catch_a_validation_exception_thrown_by_the_next_bus(
        ApplicationBusInterface $next,
        ValidatorInterface $validator,
        RequestInterface $request
    ) {
        $validator->validate($request)->shouldBeCalled();

        $next
            ->send($request)
            ->willThrow(new ValidationFailedException(['Id must be a UUID.', 'Width must be positive.']));

        $this
            ->shouldThrow(new ValidationFailedException(['Id must be a UUID.', 'Width must be positive.']))
            ->during('send', [$request]);
    }
}

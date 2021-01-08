<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Infrastructure\Bus\ApplicationBusInterface;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExceptionFilterBusSpec extends ObjectBehavior
{
    function let(ApplicationBusInterface $next, LoggerInterface $logger)
    {
        $this->beConstructedWith($next, $logger);
    }

    function it_translates_any_unexpected_exception_to_a_technical_exception(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $next->send($request)->willThrow(new \Exception('Oops'));

        $logger->critical(Argument::any(), Argument::any())->shouldBeCalled();

        $this->shouldThrow(TechnicalException::class)->during('send', [$request]);
    }

    function it_does_not_filter_application_exceptions(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $exception = new ApplicationException('Oops');

        $next->send($request)->willThrow($exception);

        $logger->critical(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->shouldThrow($exception)->during('send', [$request]);
    }
}

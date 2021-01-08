<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Bus\ApplicationBusInterface;
use GameOfLife\Infrastructure\Exception\SerializationException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use GameOfLife\Infrastructure\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;

class LoggerBusSpec extends ObjectBehavior
{
    function let(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->beConstructedWith($next, $logger, $serializer);
    }

    function it_logs_the_request_and_the_response(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $serializer->serialize($request, 'json')->willReturn('{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}');
        $serializer->serialize($response, 'json')->willReturn('{"exists":true}');

        $logger
            ->info(
                'Receiving an application request.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}',
                    ],
                ]
            )
            ->shouldBeCalled();

        $logger
            ->info(
                'The application request has been successfully processed.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}',
                    ],
                    'response' => [
                        'class' => \get_class($response->getWrappedObject()),
                        'data' => '{"exists":true}',
                    ],
                ]
            )
            ->shouldBeCalled();

        $next->send($request)->shouldBeCalled()->willReturn($response);

        $this->send($request)->shouldReturn($response);
    }

    function it_catches_serialization_exceptions(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $serializer->serialize($request, 'json')->willThrow(new SerializationException());
        $serializer->serialize($response, 'json')->willThrow(new SerializationException());

        $logger
            ->info(
                'Receiving an application request.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{}',
                    ],
                ]
            )
            ->shouldBeCalled();

        $logger
            ->info(
                'The application request has been successfully processed.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{}',
                    ],
                    'response' => [
                        'class' => \get_class($response->getWrappedObject()),
                        'data' => '{}',
                    ],
                ]
            )
            ->shouldBeCalled();

        $next->send($request)->shouldBeCalled()->willReturn($response);

        $this->send($request)->shouldReturn($response);
    }

    function it_logs_any_exception_thrown_by_the_next_bus(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RequestInterface $request
    ) {
        $exception = new \Exception('Oops');

        $serializer->serialize($request, 'json')->willReturn('{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}');

        $logger
            ->info(
                'Receiving an application request.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}',
                    ],
                ]
            )
            ->shouldBeCalled();

        $logger
            ->info(
                'An exception has been thrown during the application request process.',
                [
                    'request' => [
                        'class' => \get_class($request->getWrappedObject()),
                        'data' => '{"colony_id":"59494a9a-32cc-481e-a4f1-093a8dcef162"}',
                    ],
                    'exception' => $exception,
                ]
            )
            ->shouldBeCalled();

        $next->send($request)->shouldBeCalled()->willThrow($exception);

        $this->shouldThrow($exception)->during('send', [$request]);
    }
}

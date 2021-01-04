<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Serializer;

use GameOfLife\Infrastructure\Exception\SerializationException;
use JMS\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;

class JmsSerializerAdapterSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_forwards_the_serialization_to_the_symfony_component(
        SerializerInterface $serializer
    ) {
        $object = new \stdClass();

        $serializer->serialize($object, 'json')->shouldBeCalled()->willReturn('{"id":1224}');

        $this->serialize($object, 'json')->shouldReturn('{"id":1224}');
    }

    function it_throws_a_exception_if_anything_goes_wrong_during_serialization(
        SerializerInterface $serializer
    ) {
        $object = new \stdClass();

        $serializer->serialize($object, 'json')->willThrow(new \Exception('Oops'));

        $this
            ->shouldThrow(SerializationException::class)
            ->during('serialize', [$object, 'json']);
    }

    function it_forwards_the_deserialization_to_the_symfony_component(
        SerializerInterface $serializer
    ) {
        $object = new \stdClass();

        $serializer->deserialize('{"id":1224}', 'App\\Domain\\Entity', 'json')->shouldBeCalled()->willReturn($object);

        $this->deserialize('{"id":1224}', 'App\\Domain\\Entity', 'json')->shouldReturn($object);
    }

    function it_throws_a_exception_if_anything_goes_wrong_during_deserialization(
        SerializerInterface $serializer
    ) {
        $serializer->deserialize('{"id":1224}', 'App\\Domain\\Entity', 'json')->willThrow(new \Exception('Oops'));

        $this
            ->shouldThrow(SerializationException::class)
            ->during('deserialize', ['{"id":1224}', 'App\\Domain\\Entity', 'json']);
    }
}

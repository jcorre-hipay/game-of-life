<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Serializer;

use GameOfLife\Infrastructure\Exception\SerializationException;

class JmsSerializerAdapter implements SerializerInterface
{
    private $serializer;

    /**
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(\JMS\Serializer\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param object $data
     * @param string $format
     * @return string
     * @throws SerializationException
     */
    public function serialize($data, string $format): string
    {
        try {
            return $this->serializer->serialize($data, $format);
        } catch (\Exception $exception) {
            throw new SerializationException('Serialization failed.', 0, $exception);
        }
    }

    /**
     * @param string $data
     * @param string $type
     * @param string $format
     * @return object
     * @throws SerializationException
     */
    public function deserialize(string $data, string $type, string $format)
    {
        try {
            return $this->serializer->deserialize($data, $type, $format);
        } catch (\Exception $exception) {
            throw new SerializationException('Deserialization failed.', 0, $exception);
        }
    }
}

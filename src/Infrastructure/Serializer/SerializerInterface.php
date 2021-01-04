<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Serializer;

use GameOfLife\Infrastructure\Exception\SerializationException;

interface SerializerInterface
{
    /**
     * @param object $data
     * @param string $format
     * @return string
     * @throws SerializationException
     */
    public function serialize($data, string $format): string;

    /**
     * @param string $data
     * @param string $type
     * @param string $format
     * @return object
     * @throws SerializationException
     */
    public function deserialize(string $data, string $type, string $format);
}

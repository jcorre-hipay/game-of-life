<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Exception\SerializationException;
use GameOfLife\Infrastructure\Logger\LoggerInterface;
use GameOfLife\Infrastructure\Serializer\SerializerInterface;

class LoggerBus implements ApplicationBusInterface
{
    private $next;
    private $logger;
    private $serializer;

    /**
     * @param ApplicationBusInterface $next
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ApplicationBusInterface $next,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->next = $next;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            $this->logger->info(
                'Receiving an application request.',
                [
                    'request' => $this->serialize($request),
                ]
            );

            $response = $this->next->send($request);

            $this->logger->info(
                'The application request has been successfully processed.',
                [
                    'request' => $this->serialize($request),
                    'response' => $this->serialize($response),
                ]
            );

            return $response;
        } catch (\Exception $exception) {
            $this->logger->info(
                'An exception has been thrown during the application request process.',
                [
                    'request' => $this->serialize($request),
                    'exception' => $exception,
                ]
            );

            throw $exception;
        }
    }

    /**
     * @param mixed $object
     * @return mixed
     */
    private function serialize($object)
    {
        if ($object instanceof \Traversable) {
            $result = [];

            foreach ($object as $item) {
                $result[] = $this->serialize($item);
            }

            return $result;
        }

        if (\is_object($object)) {
            return [
                'class' => \get_class($object),
                'data' => $this->serializeObjectContent($object),
            ];
        }

        return \json_encode($object);
    }

    /**
     * @param object $object
     * @return string
     */
    private function serializeObjectContent($object): string
    {
        try {
            return $this->serializer->serialize($object, 'json');
        } catch (SerializationException $exception) {
            return '{}';
        }
    }
}

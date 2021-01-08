<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\TechnicalException;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Logger\LoggerInterface;

class ExceptionFilterBus implements ApplicationBusInterface
{
    private $next;
    private $logger;

    /**
     * @param ApplicationBusInterface $next
     */
    public function __construct(ApplicationBusInterface $next, LoggerInterface $logger)
    {
        $this->next = $next;
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ApplicationException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->next->send($request);
        } catch (ApplicationException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->critical(
                'An unexpected excepted has been thrown by the application.',
                [
                    'exceptions' => $this->formatException($exception),
                ]
            );

            throw new TechnicalException('Unexpected exception.', 0, $exception);
        }
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    private function formatException(\Exception $exception): array
    {
        $details = [];

        while ($exception instanceof \Throwable) {
            $details[] = [
                'class' => \get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];

            $exception = $exception->getPrevious();
        }

        return $details;
    }
}

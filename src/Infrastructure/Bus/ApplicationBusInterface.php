<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;

interface ApplicationBusInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ApplicationException
     */
    public function send(RequestInterface $request): ResponseInterface;
}

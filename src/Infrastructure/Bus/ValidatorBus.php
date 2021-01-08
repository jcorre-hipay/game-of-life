<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Exception\ValidationFailedException;
use GameOfLife\Infrastructure\Validator\ValidatorInterface;

class ValidatorBus implements ApplicationBusInterface
{
    private $next;
    private $validator;

    /**
     * @param ApplicationBusInterface $next
     * @param ValidatorInterface $validator
     */
    public function __construct(ApplicationBusInterface $next, ValidatorInterface $validator)
    {
        $this->next = $next;
        $this->validator = $validator;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ApplicationException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);
        } catch (ValidationFailedException $exception) {
            throw new InvalidParametersException($exception->getViolations());
        }

        return $this->next->send($request);
    }
}

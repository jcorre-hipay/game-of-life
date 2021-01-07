<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Bus;

use GameOfLife\Application\Command\CommandHandlerInterface;
use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\HandlerInterface;
use GameOfLife\Application\Query\QueryHandlerInterface;
use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Application\RequestInterface;
use GameOfLife\Application\ResponseInterface;
use GameOfLife\Infrastructure\Exception\HandlerNotFoundException;

class DispatcherBus implements ApplicationBusInterface
{
    private $queryHandlers;
    private $commandHandlers;

    public function __construct()
    {
        $this->queryHandlers = [];
        $this->commandHandlers = [];
    }

    /**
     * @param HandlerInterface $handler
     */
    public function register(HandlerInterface $handler): void
    {
        if ($handler instanceof CommandHandlerInterface) {
            $this->commandHandlers[$handler->respondTo()] = $handler;
        }

        if ($handler instanceof QueryHandlerInterface) {
            $this->queryHandlers[$handler->respondTo()] = $handler;
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ApplicationException
     * @throws HandlerNotFoundException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $requestClassName = \get_class($request);

        if ($request instanceof CommandInterface) {
            return $this->getCommandHandler($requestClassName)->execute($request);
        }

        if ($request instanceof QueryInterface) {
            return $this->getQueryHandler($requestClassName)->execute($request);
        }

        throw new HandlerNotFoundException(\sprintf('Unsupported application request type "%s".', $requestClassName));
    }

    /**
     * @param string $commandClassName
     * @return CommandHandlerInterface
     * @throws HandlerNotFoundException
     */
    private function getCommandHandler(string $commandClassName): CommandHandlerInterface
    {
        if (!isset($this->commandHandlers[$commandClassName])) {
            throw new HandlerNotFoundException(
                \sprintf('No handlers have been registered for the command "%s".', $commandClassName)
            );
        }

        return $this->commandHandlers[$commandClassName];
    }

    /**
     * @param string $queryClassName
     * @return QueryHandlerInterface
     * @throws HandlerNotFoundException
     */
    private function getQueryHandler(string $queryClassName): QueryHandlerInterface
    {
        if (!isset($this->queryHandlers[$queryClassName])) {
            throw new HandlerNotFoundException(
                \sprintf('No handlers have been registered for the query "%s".', $queryClassName)
            );
        }

        return $this->queryHandlers[$queryClassName];
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Exception;

class ValidationFailedException extends InfrastructureException
{
    private $violations;

    /**
     * @param array $violations
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $violations,
        string $message = 'Validation has failed.',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->violations = $violations;
    }

    /**
     * @return array
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}

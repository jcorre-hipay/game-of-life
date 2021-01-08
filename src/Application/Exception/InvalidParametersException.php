<?php

declare(strict_types=1);

namespace GameOfLife\Application\Exception;

class InvalidParametersException extends ApplicationException
{
    private $errors;

    /**
     * @param string[] $errors
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $errors,
        string $message = 'Invalid parameters.',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

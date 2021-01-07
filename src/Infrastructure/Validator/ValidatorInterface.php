<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator;

use GameOfLife\Infrastructure\Exception\ValidationFailedException;

interface ValidatorInterface
{
    /**
     * @param object $object
     * @throws ValidationFailedException
     */
    public function validate($object): void;
}

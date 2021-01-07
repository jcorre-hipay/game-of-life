<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator;

use GameOfLife\Infrastructure\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

class SymfonyValidatorAdapter implements ValidatorInterface
{
    private $validator;

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(\Symfony\Component\Validator\Validator\ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param object $object
     * @throws ValidationFailedException
     */
    public function validate($object): void
    {
        $errors = $this->validator->validate($object);

        if (\count($errors) > 0) {
            $violations = [];

            foreach ($errors as $error) {
                /** @var ConstraintViolationInterface $error */
                $violations[] = $error->getMessage();
            }

            throw new ValidationFailedException($violations);
        }
    }
}

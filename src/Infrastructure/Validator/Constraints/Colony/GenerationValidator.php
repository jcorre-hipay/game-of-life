<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class GenerationValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Generation) {
            throw new UnexpectedTypeException($constraint, Generation::class);
        }

        if (null === $value) {
            if (!$constraint->nullable) {
                $this->addViolation($value, $constraint);
            }

            return;
        }

        if (!\is_int($value)) {
            throw new UnexpectedValueException($value, 'int');
        }

        if ($value < 0) {
            $this->addViolation($value, $constraint);
        }
    }

    /**
     * @param int|null $value
     * @param Generation $constraint
     */
    private function addViolation(?int $value, Generation $constraint): void
    {
        $this
            ->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ value }}', \json_encode($value))
            ->addViolation();
    }
}

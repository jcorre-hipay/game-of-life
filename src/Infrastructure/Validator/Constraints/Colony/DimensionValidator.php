<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DimensionValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Dimension) {
            throw new UnexpectedTypeException($constraint, Dimension::class);
        }

        if (!\is_int($value)) {
            throw new UnexpectedValueException($value, 'int');
        }

        if ($value <= 0) {
            $this
                ->context
                ->buildViolation(\str_replace('%dimension%', $constraint->dimension, $constraint->message))
                ->setParameter('{{ value }}', \json_encode($value))
                ->addViolation();
        }
    }
}

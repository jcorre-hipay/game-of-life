<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CellCountMatchesDimensionsValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CellCountMatchesDimensions) {
            throw new UnexpectedTypeException($constraint, CellCountMatchesDimensions::class);
        }

        $width = \method_exists($value, 'getWidth') ? $value->getWidth() : 0;
        $height = \method_exists($value, 'getHeight') ? $value->getHeight() : 0;
        $cellStates = \method_exists($value, 'getCellStates') ? $value->getCellStates() : [];

        if (\count($cellStates) !== $width * $height) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

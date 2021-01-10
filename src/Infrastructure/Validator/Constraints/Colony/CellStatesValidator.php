<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Domain\Colony\CellState;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CellStatesValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CellStates) {
            throw new UnexpectedTypeException($constraint, CellStates::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        foreach ($value as $state) {
            if (!\in_array($state, [CellState::DEAD, CellState::LIVE], true)) {
                $this
                    ->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', \json_encode($value))
                    ->addViolation();

                break;
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CellStates extends Constraint
{
    public $message = 'A cell should be either live or dead.';
}

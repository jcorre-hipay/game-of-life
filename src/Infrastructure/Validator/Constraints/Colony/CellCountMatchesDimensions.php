<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CellCountMatchesDimensions extends Constraint
{
    public $message = 'The number of cells should correspond to the width and height.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

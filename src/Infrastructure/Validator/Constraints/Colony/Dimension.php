<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Dimension extends Constraint
{
    public $message = 'The %dimension% should be a positive number.';
    public $dimension = 'dimension';
}

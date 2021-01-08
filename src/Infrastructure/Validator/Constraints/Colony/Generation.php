<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Generation extends Constraint
{
    public $message = 'The generation should be a positive or nul number.';
    public $nullable = false;
}

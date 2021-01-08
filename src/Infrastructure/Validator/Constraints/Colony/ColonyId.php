<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Validator\Constraints\Colony;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ColonyId extends Constraint
{
    public $message = 'The colony id should follow the uuid format.';
}

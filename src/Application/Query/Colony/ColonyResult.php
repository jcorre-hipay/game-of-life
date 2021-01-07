<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Application\AbstractCollection;
use GameOfLife\Application\Query\ResultInterface;

class ColonyResult extends AbstractCollection implements ResultInterface
{
    /**
     * @param mixed $item
     * @return bool
     */
    protected function supports($item): bool
    {
        return $item instanceof Colony;
    }
}

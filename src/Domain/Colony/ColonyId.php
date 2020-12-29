<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Core\EntityIdInterface;

class ColonyId implements EntityIdInterface
{
    /**
     * @return string
     */
    public function toString(): string
    {
        return '59494a9a-32cc-481e-a4f1-093a8dcef162';
    }

    /**
     * @param EntityIdInterface $other
     * @return bool
     */
    public function equals(EntityIdInterface $other): bool
    {
        return false;
    }
}

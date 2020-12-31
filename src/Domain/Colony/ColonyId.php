<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

use GameOfLife\Domain\Core\EntityIdInterface;

class ColonyId implements EntityIdInterface
{
    private $id;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->id;
    }

    /**
     * @param EntityIdInterface $other
     * @return bool
     */
    public function equals(EntityIdInterface $other): bool
    {
        return $other instanceof self && $other->toString() === $this->toString();
    }
}

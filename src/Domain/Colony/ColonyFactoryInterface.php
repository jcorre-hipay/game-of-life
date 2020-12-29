<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

interface ColonyFactoryInterface
{
    /**
     * @param ColonyId $id
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     * @return ColonyInterface
     */
    public function create(ColonyId $id, int $width, int $height, array $cellStates): ColonyInterface;
}

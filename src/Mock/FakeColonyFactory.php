<?php

declare(strict_types=1);

namespace GameOfLife\Mock;

use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Colony\ColonyInterface;

class FakeColonyFactory implements ColonyFactoryInterface
{
    /**
     * @param ColonyId $id
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     * @return ColonyInterface
     */
    public function create(ColonyId $id, int $width, int $height, array $cellStates): ColonyInterface
    {
        return new FakeColony($id, $width, $height, $cellStates);
    }
}

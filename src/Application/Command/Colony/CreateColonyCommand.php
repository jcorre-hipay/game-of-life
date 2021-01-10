<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\CommandInterface;
use GameOfLife\Infrastructure\Validator\Constraints as Assert;

/**
 * @Assert\Colony\CellCountMatchesDimensions
 */
class CreateColonyCommand implements CommandInterface
{
    /**
     * @Assert\Colony\Dimension(dimension="width")
     */
    private $width;

    /**
     * @Assert\Colony\Dimension(dimension="height")
     */
    private $height;

    /**
     * @Assert\Colony\CellStates
     */
    private $cellStates;

    /**
     * @param int $width
     * @param int $height
     * @param string[] $cellStates
     */
    public function __construct(int $width, int $height, array $cellStates)
    {
        $this->width = $width;
        $this->height = $height;
        $this->cellStates = $cellStates;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return string[]
     */
    public function getCellStates(): array
    {
        return $this->cellStates;
    }
}

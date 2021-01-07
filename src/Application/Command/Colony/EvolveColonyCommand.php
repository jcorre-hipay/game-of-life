<?php

declare(strict_types=1);

namespace GameOfLife\Application\Command\Colony;

use GameOfLife\Application\Command\CommandInterface;

class EvolveColonyCommand implements CommandInterface
{
    private $colonyId;

    /**
     * @param string $colonyId
     */
    public function __construct(string $colonyId)
    {
        $this->colonyId = $colonyId;
    }

    /**
     * @return string
     */
    public function getColonyId(): string
    {
        return $this->colonyId;
    }
}

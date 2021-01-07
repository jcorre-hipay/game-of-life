<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Domain\Colony\ColonyInterface;

class Colony
{
    private $colony;

    /**
     * @param ColonyInterface $colony
     */
    public function __construct(ColonyInterface $colony)
    {
        $this->colony = $colony;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->colony->getId()->toString();
    }

    /**
     * @return int
     */
    public function getGeneration(): int
    {
        return $this->colony->getGeneration();
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->colony->getWidth();
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->colony->getHeight();
    }

    /**
     * @return string[]
     */
    public function getCellStates(): array
    {
        return $this->colony->getCellStates();
    }
}

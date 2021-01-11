<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Domain\Colony\ColonyInterface;

class Colony
{
    private $colony;
    private $lastGeneration;

    /**
     * @param ColonyInterface $colony
     * @param int $lastGeneration
     */
    public function __construct(ColonyInterface $colony, int $lastGeneration)
    {
        $this->colony = $colony;
        $this->lastGeneration = $lastGeneration;
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
    public function getLastGeneration(): int
    {
        return $this->lastGeneration;
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

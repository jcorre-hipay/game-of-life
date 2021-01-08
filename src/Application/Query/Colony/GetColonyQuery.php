<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Application\Query\QueryInterface;
use GameOfLife\Infrastructure\Validator\Constraints as Assert;

class GetColonyQuery implements QueryInterface
{
    /**
     * @Assert\Colony\ColonyId
     */
    private $colonyId;

    /**
     * @Assert\Colony\Generation(nullable=true)
     */
    private $generation;

    /**
     * @param string $colonyId
     * @param int|null $generation
     */
    public function __construct(string $colonyId, ?int $generation = null)
    {
        $this->colonyId = $colonyId;
        $this->generation = $generation;
    }

    /**
     * @return string
     */
    public function getColonyId(): string
    {
        return $this->colonyId;
    }

    /**
     * @return int|null
     */
    public function getGeneration(): ?int
    {
        return $this->generation;
    }
}

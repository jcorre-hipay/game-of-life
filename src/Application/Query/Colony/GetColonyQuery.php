<?php

declare(strict_types=1);

namespace GameOfLife\Application\Query\Colony;

use GameOfLife\Application\Query\QueryInterface;

class GetColonyQuery implements QueryInterface
{
    private $colonyId;
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

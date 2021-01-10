<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Colony;

interface GenerateCellStateInterface
{
    /**
     * @return string
     */
    public function execute(): string;
}

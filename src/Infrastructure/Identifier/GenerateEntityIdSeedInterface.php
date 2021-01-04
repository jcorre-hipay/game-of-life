<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Identifier;

interface GenerateEntityIdSeedInterface
{
    /**
     * @return string
     */
    public function execute(): string;
}

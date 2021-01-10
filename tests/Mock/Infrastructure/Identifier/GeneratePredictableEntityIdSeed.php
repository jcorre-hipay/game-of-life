<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Mock\Infrastructure\Identifier;

use GameOfLife\Infrastructure\Identifier\GenerateEntityIdSeedInterface;

class GeneratePredictableEntityIdSeed implements GenerateEntityIdSeedInterface
{
    private static $sequence = [];

    /**
     * @return string
     */
    public function execute(): string
    {
        return \array_shift(static::$sequence);
    }

    /**
     * @param string[] $sequence
     */
    public function set(array $sequence): void
    {
        static::$sequence = $sequence;
    }
}

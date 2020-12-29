<?php

declare(strict_types=1);

namespace GameOfLife\Domain\Colony;

interface CellState
{
    public const LIVE = 'live';
    public const DEAD = 'dead';
}

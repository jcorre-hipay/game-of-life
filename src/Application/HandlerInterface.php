<?php

declare(strict_types=1);

namespace GameOfLife\Application;

interface HandlerInterface
{
    /**
     * @return string
     */
    public function respondTo(): string;
}

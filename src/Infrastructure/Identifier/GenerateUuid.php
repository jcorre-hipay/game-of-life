<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Identifier;

use Ramsey\Uuid\Uuid;

class GenerateUuid implements GenerateEntityIdSeedInterface
{
    /**
     * @return string
     */
    public function execute(): string
    {
        return Uuid::uuid4()->toString();
    }
}

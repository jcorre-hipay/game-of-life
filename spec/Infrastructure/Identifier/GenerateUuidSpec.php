<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Identifier;

use PhpSpec\ObjectBehavior;

class GenerateUuidSpec extends ObjectBehavior
{
    function it_generates_a_uuid()
    {
        $this->execute()->shouldMatch('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/');
    }

    function it_generates_unique_identifiers()
    {
        $this->execute()->shouldNotReturn($this->execute());
    }
}

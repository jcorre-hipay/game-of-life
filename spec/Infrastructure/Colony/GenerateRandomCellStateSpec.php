<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Colony;

use PhpSpec\ObjectBehavior;

class GenerateRandomCellStateSpec extends ObjectBehavior
{
    function it_generates_a_random_cell_state()
    {
        $this->execute()->shouldBeIn(['live', 'dead']);
    }

    public function getMatchers(): array
    {
        return [
            'beIn' => function (string $subject, array $values) {
                return \in_array($subject, $values, true);
            }
        ];
    }
}

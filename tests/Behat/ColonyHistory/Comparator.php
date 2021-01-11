<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use GameOfLife\Domain\Colony\CellState;
use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\DomainEventInterface;
use GameOfLife\Tests\Behat\Exception\ComparisonException;

class Comparator
{
    /**
     * @param ColonyId $colonyId
     * @param \DateTimeInterface $dateTime
     * @param string[] $current
     * @param string[] $next
     * @return DomainEventInterface[]
     */
    public function execute(ColonyId $colonyId, \DateTimeInterface $dateTime, array $current, array $next): array
    {
        if (\count($current) !== \count($next)) {
            throw new ComparisonException(
                \sprintf('Invalid cell count between two colonies: got %d and %d.', \count($current), \count($next))
            );
        }

        $events = [];

        for ($index = 0; $index < \count($current); $index++) {
            if (CellState::LIVE === $current[$index] && CellState::DEAD === $next[$index]) {
                $events[] = new CellDied($colonyId, $dateTime, $index);
                continue;
            }

            if (CellState::DEAD === $current[$index] && CellState::LIVE === $next[$index]) {
                $events[] = new CellBorn($colonyId, $dateTime, $index);
                continue;
            }
        }

        return $events;
    }
}

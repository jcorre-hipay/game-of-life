<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Tests\Behat\Exception\ComparisonException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ComparatorTest extends TestCase
{
    /**
     * @var Comparator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Comparator();
    }

    /**
     * @test
     */
    public function itReturnsAnEmptyArrayWhenTheTwoColoniesAreIdentical(): void
    {
        $colonyId = $this->createMock(ColonyId::class);
        $now = new \DateTime();

        $events = $this->subject->execute($colonyId, $now, ['dead', 'live', 'dead'], ['dead', 'live', 'dead']);

        Assert::assertSame([], $events);
    }

    /**
     * @test
     */
    public function itReturnsTheDomainEventsToApplyToEvolveTheFirstColonyToTheSecondOne(): void
    {
        $colonyId = $this->createMock(ColonyId::class);
        $now = new \DateTime();

        $events = $this->subject->execute($colonyId, $now, ['dead', 'live', 'dead'], ['dead', 'dead', 'live']);

        Assert::assertCount(2, $events);

        Assert::assertInstanceOf(CellDied::class, $events[0]);
        Assert::assertSame($colonyId, $events[0]->getEntityId());
        Assert::assertSame($now, $events[0]->getEventDate());
        Assert::assertSame(1, $events[0]->getIndex());

        Assert::assertInstanceOf(CellBorn::class, $events[1]);
        Assert::assertSame($colonyId, $events[1]->getEntityId());
        Assert::assertSame($now, $events[1]->getEventDate());
        Assert::assertSame(2, $events[1]->getIndex());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTheCellCountIsDifferentFromTheTwoColonies(): void
    {
        $colonyId = $this->createMock(ColonyId::class);
        $now = new \DateTime();

        try {
            $this->subject->execute($colonyId, $now, ['dead', 'live', 'dead'], ['live', 'live']);
        } catch (ComparisonException $exception) {
            Assert::assertSame('Invalid cell count between two colonies: got 3 and 2.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

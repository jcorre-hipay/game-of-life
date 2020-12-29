<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use GameOfLife\Tests\Behat\Exception\ColonyConsistencyViolationException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Builder();
    }

    /**
     * @test
     */
    public function itBuildsAnEmptyHistoryIfNoDataAreCommitted(): void
    {
        Assert::assertSame([], $this->subject->build());
    }

    /**
     * @test
     */
    public function itBuildsColonyHistoryFragments(): void
    {
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $expected = [
            [
                'cell_states' => ['live', 'dead', 'dead', 'dead', 'dead', 'dead'],
                'generation' => 0,
                'height' => 2,
                'width' => 3,
            ],
            [
                'cell_states' => ['live', 'live', 'dead', 'live', 'live', 'live'],
                'generation' => 1,
                'height' => 2,
                'width' => 3,
            ]
        ];

        Assert::assertSame($expected, $this->subject->build());

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $expected = [
            [
                'cell_states' => ['dead', 'dead', 'live', 'dead', 'live', 'live'],
                'generation' => 2,
                'height' => 2,
                'width' => 3,
            ]
        ];

        Assert::assertSame($expected, $this->subject->build());
    }

    /**
     * @test
     */
    public function itValidatesTheColonyWidthDependingOnTheFirstSegment(): void
    {
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');

        try {
            $this->subject->storeSegment();
        } catch (ColonyConsistencyViolationException $exception) {
            Assert::assertSame('Invalid width at generation 1: expected 3 but got 4.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }

    /**
     * @test
     */
    public function itDoesNotResetTheWidthConstraintAfterBuildingAnHistoryFragment(): void
    {
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->build();

        $this->subject->addCellToCurrentSegment('dead');

        try {
            $this->subject->storeSegment();
        } catch (ColonyConsistencyViolationException $exception) {
            Assert::assertSame('Invalid width at generation 2: expected 2 but got 1.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }

    /**
     * @test
     */
    public function itValidatesTheSegmentCountDependingOnTheFirstLine(): void
    {
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        try {
            $this->subject->commitSegments();
        } catch (ColonyConsistencyViolationException $exception) {
            Assert::assertSame('Invalid segment count at line 2: expected 2 but got 3.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }

    /**
     * @test
     */
    public function itValidatesTheColonyHeightDependingOnTheFirstHistoryFragment(): void
    {
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->storeSegment();

        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        $this->subject->build();

        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('dead');
        $this->subject->addCellToCurrentSegment('live');
        $this->subject->storeSegment();

        $this->subject->commitSegments();

        try {
            $this->subject->build();
        } catch (ColonyConsistencyViolationException $exception) {
            Assert::assertSame('Invalid height at generation 2: expected 2 but got 1.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

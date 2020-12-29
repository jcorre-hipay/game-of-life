<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use GameOfLife\Tests\Behat\Exception\ColonyConsistencyViolationException;

class Builder
{
    private $colonies;
    private $segments;
    private $currentSegment;
    private $generation;
    private $line;
    private $width;
    private $height;

    public function __construct()
    {
        $this->colonies = [];
        $this->segments = [];
        $this->currentSegment = [];
        $this->generation = 0;
        $this->line = 1;
        $this->width = 0;
        $this->height = 0;
    }

    /**
     * @param string $state
     */
    public function addCellToCurrentSegment(string $state): void
    {
        $this->currentSegment[] = $state;
    }

    public function storeSegment(): void
    {
        $currentSegmentWidth = \count($this->currentSegment);

        if (0 === $this->width) {
            $this->width = $currentSegmentWidth;
        }

        if ($currentSegmentWidth !== $this->width) {
            throw new ColonyConsistencyViolationException(
                \sprintf(
                    'Invalid width at generation %d: expected %d but got %d.',
                    $this->generation + \count($this->segments),
                    $this->width,
                    $currentSegmentWidth
                )
            );
        }

        $this->segments[] = $this->currentSegment;
        $this->currentSegment = [];
    }

    public function commitSegments(): void
    {
        if (empty($this->colonies)) {
            $this->colonies = \array_fill(0, \count($this->segments), []);
        }

        if (\count($this->colonies) !== \count($this->segments)) {
            throw new ColonyConsistencyViolationException(
                \sprintf(
                    'Invalid segment count at line %d: expected %d but got %d.',
                    $this->line,
                    \count($this->colonies),
                    \count($this->segments)
                )
            );
        }

        foreach ($this->segments as $index => $segment) {
            $this->colonies[$index] = \array_merge($this->colonies[$index], $segment);
        }

        $this->segments = [];
        $this->line += 1;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        if (0 === $this->height && !empty($this->colonies)) {
            $this->height = $this->getColonyHeight($this->colonies[0]);
        }

        $history = [];

        foreach ($this->colonies as $colony) {

            if ($this->getColonyHeight($colony) !== $this->height) {
                throw new ColonyConsistencyViolationException(
                    \sprintf(
                        'Invalid height at generation %d: expected %d but got %d.',
                        $this->generation,
                        $this->height,
                        $this->getColonyHeight($colony)
                    )
                );
            }

            $history[] = [
                'cell_states' => $colony,
                'generation' => $this->generation,
                'height' => $this->height,
                'width' => $this->width,
            ];

            $this->generation += 1;
        }

        $this->colonies = [];

        return $history;
    }

    /**
     * @param array $colony
     * @return int
     */
    private function getColonyHeight(array $colony): int
    {
        return \intval(\count($colony) / \max(1, $this->width));
    }
}

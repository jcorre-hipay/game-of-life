<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use GameOfLife\Domain\Colony\CellState;

class Parser
{
    private $lexer;
    private $handlers;

    /**
     * @param Lexer $lexer
     */
    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->handlers = [
            Lexer::LIVE_CELL => function (Builder $builder): array {
                return $this->handleLiveCellToken($builder);
            },
            Lexer::DEAD_CELL => function (Builder $builder): array {
                return $this->handleDeadCellToken($builder);
            },
            Lexer::END_OF_SEGMENT => function (Builder $builder): array {
                return $this->handleEndOfSegmentToken($builder);
            },
            Lexer::END_OF_LINE => function (Builder $builder): array {
                return $this->handleEndOfLineToken($builder);
            },
            Lexer::END_OF_GROUP => function (Builder $builder): array {
                return $this->handleEndOfGroupToken($builder);
            },
        ];
    }

    /**
     * @param Builder $builder
     * @param string $payload
     * @return array
     */
    public function execute(Builder $builder, string $payload): array
    {
        $history = [];

        foreach ($this->lexer->execute($payload) as $token) {
            $history = \array_merge($history, $this->handleLexicalToken($builder, $token));
        }

        return $history;
    }

    /**
     * @param Builder $builder
     * @param string $token
     * @return array
     */
    private function handleLexicalToken(Builder $builder, string $token): array
    {
        if (!isset($this->handlers[$token])) {
            return [];
        }

        return $this->handlers[$token]($builder);
    }

    /**
     * @param Builder $builder
     * @return array
     */
    private function handleLiveCellToken(Builder $builder): array
    {
        $builder->addCellToCurrentSegment(CellState::LIVE);

        return [];
    }

    /**
     * @param Builder $builder
     * @return array
     */
    private function handleDeadCellToken(Builder $builder): array
    {
        $builder->addCellToCurrentSegment(CellState::DEAD);

        return [];
    }

    /**
     * @param Builder $builder
     * @return array
     */
    private function handleEndOfSegmentToken(Builder $builder): array
    {
        $builder->storeSegment();

        return [];
    }

    /**
     * @param Builder $builder
     * @return array
     */
    private function handleEndOfLineToken(Builder $builder): array
    {
        $builder->commitSegments();

        return [];
    }

    /**
     * @param Builder $builder
     * @return array
     */
    private function handleEndOfGroupToken(Builder $builder): array
    {
        return $builder->build();
    }
}

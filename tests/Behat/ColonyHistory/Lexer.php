<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

class Lexer
{
    public const LIVE_CELL = '[*]';
    public const DEAD_CELL = '[ ]';
    public const END_OF_SEGMENT = 'EOS';
    public const END_OF_LINE = 'EOL';
    public const END_OF_GROUP = 'EOG';

    /**
     * @param string $payload
     * @return array
     */
    public function execute(string $payload): array
    {
        $tokens = [];

        $onGroup = false;

        foreach (\explode("\n", $payload) as $line) {
            if (!\preg_match_all('/(?:\[[^\[\]]\])+/', $line, $matches)) {
                if ($onGroup) {
                    $onGroup = false;
                    $tokens[] = self::END_OF_GROUP;
                }

                continue;
            }

            $onGroup = true;
            $tokens = \array_merge($tokens, $this->parseSegments($matches[0]), [self::END_OF_LINE]);
        }

        if (!empty($tokens) && self::END_OF_LINE === $tokens[\count($tokens) - 1]) {
            $tokens[] = self::END_OF_GROUP;
        }

        return $tokens;
    }

    /**
     * @param array $segments
     * @return array
     */
    private function parseSegments(array $segments): array
    {
        $tokens = [];

        foreach ($segments as $segment) {
            \preg_match_all('/\[[^\[\]]\]/', $segment, $matches);

            foreach ($matches[0] as $match) {
                $tokens[] = \preg_replace('/[^ \[\]]/', '*', $match);
            }

            $tokens[] = self::END_OF_SEGMENT;
        }

        return $tokens;
    }
}

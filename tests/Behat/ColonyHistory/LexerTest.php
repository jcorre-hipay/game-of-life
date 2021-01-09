<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @var Lexer
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Lexer();
    }

    /**
     * @test
     */
    public function itReturnsNoTokensWhenThePayloadDoesNotContainsAnyValidData(): void
    {
        Assert::assertSame([], $this->subject->execute(""));
        Assert::assertSame([], $this->subject->execute("   \n "));
    }

    /**
     * @test
     */
    public function itParsesAColonyIntoLexicalTokens(): void
    {
        $payload = <<<EOF
            [ ][*][ ]
            [ ][*][*]
            [*][*][ ]
            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }

    /**
     * @test
     */
    public function itParsesAGroupOfColoniesIntoLexicalTokens(): void
    {
        $payload = <<<EOF
            [ ][*][ ] [ ][*][*]
            [ ][*][*] [ ][ ][*]
            [*][*][ ] [*][ ][*]
            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', '[*]', '[ ]', '[*]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }

    /**
     * @test
     */
    public function itParsesMultipleGroupsOfColoniesIntoLexicalTokens(): void
    {
        $payload = <<<EOF
            [ ][*][ ] [ ][*][*]
            [ ][*][*] [ ][ ][*]
            [*][*][ ] [*][ ][*]
            
            [ ][*][*]
            [ ][ ][*]
            [ ][*][ ]
            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', '[*]', '[ ]', '[*]', 'EOS', 'EOL',
            'EOG',
            '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[ ]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }

    /**
     * @test
     */
    public function itIgnoresExtraCharacters(): void
    {
        $payload = <<<EOF

            59494a9a-32cc-481e-a4f1-093a8dcef162
            ------------------------------------
            
                [ ][*][ ]       [ ][*][*]
                [ ][*][*]   >   [ ][ ][*]   >
                [*][*][ ]       [*][ ][*]
            
            
                [ ][*][*]
                [ ][ ][*]
                [ ][*][ ]

            Note: The following cells are invalid
                [] [[] []] [  ]

            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', '[*]', '[ ]', '[*]', 'EOS', 'EOL',
            'EOG',
            '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[ ]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }

    /**
     * @test
     */
    public function itAllowsAnythingButSpacesOrBracketsToRepresentLiveCells(): void
    {
        $payload = <<<EOF
            [ ][#][ ]
            [ ][@][-]
            [#][-][ ]
            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }

    /**
     * @test
     */
    public function itDoesNotCheckColoniesConsistency(): void
    {
        $payload = <<<EOF
            [ ][*][ ] [ ][*][*][ ]
            [ ][*][*] [*][ ][ ][*]
            [*][*][ ]
            
            [ ][*][*]
            [ ][ ]    [*][ ][*][*][ ]
            EOF;

        $expected = [
            '[ ]', '[*]', '[ ]', 'EOS', '[ ]', '[*]', '[*]', '[ ]', 'EOS', 'EOL',
            '[ ]', '[*]', '[*]', 'EOS', '[*]', '[ ]', '[ ]', '[*]', 'EOS', 'EOL',
            '[*]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
            '[ ]', '[*]', '[*]', 'EOS', 'EOL',
            '[ ]', '[ ]', 'EOS', '[*]', '[ ]', '[*]', '[*]', '[ ]', 'EOS', 'EOL',
            'EOG',
        ];

        Assert::assertSame($expected, $this->subject->execute($payload));
    }
}

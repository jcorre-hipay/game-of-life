<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat\ColonyHistory;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserTest extends KernelTestCase
{
    /**
     * @var Parser
     */
    private $subject;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->subject = static::$container->get(Parser::class);
    }

    /**
     * @test
     */
    public function itParsesASingleColony(): void
    {
        $payload = <<<EOF
            [ ][*][ ]
            [ ][*][*]
            [*][*][ ]
            EOF;

        $expected = [
            [
                'cell_states' => ['dead', 'live', 'dead', 'dead', 'live', 'live', 'live', 'live', 'dead'],
                'generation' => 0,
                'height' => 3,
                'width' => 3,
            ]
        ];

        static::assertSame($expected, $this->subject->execute(new Builder(), $payload));
    }

    /**
     * @test
     */
    public function itParsesAColonyHistory(): void
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

            EOF;

        $expected = [
            [
                'cell_states' => ['dead', 'live', 'dead', 'dead', 'live', 'live', 'live', 'live', 'dead'],
                'generation' => 0,
                'height' => 3,
                'width' => 3,
            ],
            [
                'cell_states' => ['dead', 'live', 'live', 'dead', 'dead', 'live', 'live', 'dead', 'live'],
                'generation' => 1,
                'height' => 3,
                'width' => 3,
            ],
            [
                'cell_states' => ['dead', 'live', 'live', 'dead', 'dead', 'live', 'dead', 'live', 'dead'],
                'generation' => 2,
                'height' => 3,
                'width' => 3,
            ],
        ];

        static::assertSame($expected, $this->subject->execute(new Builder(), $payload));
    }

    /**
     * @test
     */
    public function itReturnsAnEmptyHistoryWhenThePayloadDoesNotContainsAnyValidData(): void
    {
        static::assertSame([], $this->subject->execute(new Builder(), ""));
    }
}

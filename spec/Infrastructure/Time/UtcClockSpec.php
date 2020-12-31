<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Time;

use PhpSpec\ObjectBehavior;

class UtcClockSpec extends ObjectBehavior
{
    function it_creates_an_utc_datetime_of_the_current_time()
    {
        $this->getCurrentDateTime()->shouldReturnUtcDateTime('now');
    }

    public function getMatchers(): array
    {
        return [
            'returnUtcDateTime' => function (\DateTimeInterface $subject, string $value) {
                $expected = new \DateTime($value, new \DateTimeZone('UTC'));

                if ($subject->getTimezone()->getName() !== $expected->getTimezone()->getName()) {
                    return false;
                }

                return \abs($expected->getTimestamp() - $subject->getTimestamp()) < 2;
            }
        ];
    }
}

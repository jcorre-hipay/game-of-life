<?php

declare(strict_types=1);

namespace spec\GameOfLife\Domain\Colony;

use GameOfLife\Domain\Colony\ColonyId;
use GameOfLife\Domain\Core\EntityIdInterface;
use PhpSpec\ObjectBehavior;

class ColonyIdSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('59494a9a-32cc-481e-a4f1-093a8dcef162');
    }

    function it_is_serializable()
    {
        $this->toString()->shouldReturn('59494a9a-32cc-481e-a4f1-093a8dcef162');
    }

    function it_is_equals_to_another_entity_id_of_the_same_class_and_with_the_same_string_representation(
        EntityIdInterface $otherEntityId
    ) {
        $this->equals(new ColonyId('59494a9a-32cc-481e-a4f1-093a8dcef162'))->shouldReturn(true);
        $this->equals(new ColonyId('dd20bcd1-edb6-4583-8ffd-a94c28d6cc30'))->shouldReturn(false);
        $this->equals($otherEntityId)->shouldReturn(false);
    }
}

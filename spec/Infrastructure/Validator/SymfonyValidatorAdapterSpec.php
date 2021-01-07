<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator;

use GameOfLife\Infrastructure\Exception\ValidationFailedException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymfonyValidatorAdapterSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_does_nothing_when_the_validation_is_successful(
        ValidatorInterface $validator,
        \stdClass $object
    ) {
        $validator->validate($object)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->validate($object);
    }

    function it_throws_an_exception_when_the_validation_fails(
        ValidatorInterface $validator,
        ConstraintViolationInterface $violation1,
        ConstraintViolationInterface $violation2,
        \stdClass $object
    ) {
        $violation1->getMessage()->willReturn('Id must be a UUID.');
        $violation2->getMessage()->willReturn('Width must be positive.');

        $validator
            ->validate($object)
            ->shouldBeCalled()
            ->willReturn(
                new ConstraintViolationList(
                    [
                        $violation1->getWrappedObject(),
                        $violation2->getWrappedObject(),
                    ]
                )
            );

        $this
            ->shouldThrow(new ValidationFailedException(['Id must be a UUID.', 'Width must be positive.']))
            ->during('validate', [$object]);
    }
}

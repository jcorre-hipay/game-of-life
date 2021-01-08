<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Infrastructure\Validator\Constraints\Colony\ColonyId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ColonyIdValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_only_validates_its_related_constraint(
        Constraint $constraint
    ) {
        $this
            ->shouldThrow(UnexpectedTypeException::class)
            ->during('validate', ['59494a9a-32cc-481e-a4f1-093a8dcef162', $constraint]);
    }

    function it_only_validates_string_values()
    {
        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', [42, new ColonyId()]);
    }

    function it_validates_uuid(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('59494a9a-32cc-481e-a4f1-093a8dcef162', new ColonyId());
    }

    function it_adds_a_violation_to_its_context_when_the_validation_fails(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('The colony id should follow the uuid format.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', '1224')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('1224', new ColonyId());
    }
}

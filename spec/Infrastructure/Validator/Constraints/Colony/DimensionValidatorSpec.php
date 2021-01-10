<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Infrastructure\Validator\Constraints\Colony\Dimension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DimensionValidatorSpec extends ObjectBehavior
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
            ->during('validate', [16, $constraint]);
    }

    function it_only_validates_integer_values()
    {
        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', ['16', new Dimension()]);
    }

    function it_validate_a_positive_number(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(16, new Dimension());
    }

    function it_adds_a_violation_to_its_context_when_the_value_is_nul(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('The width should be a positive number.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', '0')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $constraint = new Dimension();
        $constraint->dimension = 'width';

        $this->validate(0, $constraint);
    }

    function it_adds_a_violation_to_its_context_when_the_value_is_negative(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('The height should be a positive number.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', '-1')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $constraint = new Dimension();
        $constraint->dimension = 'height';

        $this->validate(-1, $constraint);
    }
}

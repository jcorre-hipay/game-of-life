<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Infrastructure\Validator\Constraints\Colony\Generation;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class GenerationValidatorSpec extends ObjectBehavior
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
            ->during('validate', [42, $constraint]);
    }

    function it_only_validates_integer_values()
    {
        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', ['42', new Generation()]);
    }

    function it_validates_a_null_value_when_the_constraint_is_nullable(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $constraint = new Generation();
        $constraint->nullable = true;

        $this->validate(null, $constraint);
    }

    function it_validates_a_nul_number(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(0, new Generation());
    }

    function it_validate_a_positive_number(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(42, new Generation());
    }

    function it_adds_a_violation_to_its_context_when_the_constraint_is_not_nullable_and_the_value_is_null(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('The generation should be a positive or nul number.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', 'null')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(null, new Generation());
    }

    function it_adds_a_violation_to_its_context_when_the_value_is_negative(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('The generation should be a positive or nul number.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', '-1')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(-1, new Generation());
    }
}

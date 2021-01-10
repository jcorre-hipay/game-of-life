<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Infrastructure\Validator\Constraints\Colony\CellStates;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CellStatesValidatorSpec extends ObjectBehavior
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
            ->during('validate', [['live', 'dead', 'live'], $constraint]);
    }

    function it_only_validates_array_values()
    {
        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', ['dead', new CellStates()]);
    }

    function it_validates_an_array_containing_only_live_or_dead_strings(
        ExecutionContextInterface $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(['live', 'dead', 'live'], new CellStates());
    }

    function it_adds_a_violation_to_its_context_when_the_value_is_nul(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context
            ->buildViolation('A cell should be either live or dead.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder
            ->setParameter('{{ value }}', '["live","undead"]')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['live', 'undead'], new CellStates());
    }
}

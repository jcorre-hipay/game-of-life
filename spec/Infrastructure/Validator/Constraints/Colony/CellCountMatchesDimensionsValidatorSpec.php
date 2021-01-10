<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Validator\Constraints\Colony;

use GameOfLife\Application\Command\Colony\CreateColonyCommand;
use GameOfLife\Infrastructure\Validator\Constraints\Colony\CellCountMatchesDimensions;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CellCountMatchesDimensionsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_only_validates_its_related_constraint(
        Constraint $constraint,
        CreateColonyCommand $command
    ) {
        $this
            ->shouldThrow(UnexpectedTypeException::class)
            ->during('validate', [$command, $constraint]);
    }

    function it_validates_an_object_having_a_cell_state_count_equals_to_its_width_times_its_height(
        ExecutionContextInterface $context,
        CreateColonyCommand $command
    ) {
        $command->getWidth()->willReturn(3);
        $command->getHeight()->willReturn(2);
        $command->getCellStates()->willReturn(['live', 'dead', 'live', 'live', 'dead', 'live']);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new CellCountMatchesDimensions());
    }

    function it_adds_a_violation_to_its_context_when_the_dimensions_does_not_correspond(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        CreateColonyCommand $command
    ) {
        $command->getWidth()->willReturn(16);
        $command->getHeight()->willReturn(9);
        $command->getCellStates()->willReturn(['live', 'dead', 'live', 'live', 'dead', 'live']);

        $context
            ->buildViolation('The number of cells should correspond to the width and height.')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($command, new CellCountMatchesDimensions());
    }
}

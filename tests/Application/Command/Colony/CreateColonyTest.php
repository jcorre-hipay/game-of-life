<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\CreateColonyCommand;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Bus\CommandBusInterface;
use GameOfLife\Tests\Mock\Infrastructure\Identifier\GeneratePredictableEntityIdSeed;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateColonyTest extends KernelTestCase
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var ColonyRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->commandBus = static::$container->get(CommandBusInterface::class);
        $this->repository = static::$container->get(ColonyRepositoryInterface::class);

        $generator = static::$container->get(GeneratePredictableEntityIdSeed::class);
        $generator->set(['59494a9a-32cc-481e-a4f1-093a8dcef162']);
    }

    protected function tearDown(): void
    {
        try {
            $this->repository->remove($this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));
        } catch (ColonyDoesNotExistException $exception) {
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function itCreatesANewColony(): void
    {
        $command = new CreateColonyCommand(3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live']);

        $events = $this->commandBus->send($command);

        Assert::assertInstanceOf(DomainEventCollection::class, $events);
        Assert::assertCount(1, $events);

        /** @var ColonyCreated $event */
        $event = \current($events);

        Assert::assertInstanceOf(ColonyCreated::class, $event);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $event->getEntityId()->toString());
        Assert::assertSame(0, $event->getGeneration());
        Assert::assertSame(3, $event->getWidth());
        Assert::assertSame(2, $event->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $event->getCellStates());

        $colony = $this->repository->find($this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));

        Assert::assertInstanceOf(ColonyInterface::class, $colony);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $colony->getId()->toString());
        Assert::assertSame(0, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $colony->getCellStates());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenCommandIsNotValid(): void
    {
        $command = new CreateColonyCommand(0, -1, ['undead', 'undead', 'undead']);

        try {
            $this->commandBus->send($command);
        } catch (InvalidParametersException $exception) {
            Assert::assertSame(
                [
                    'The number of cells should correspond to the width and height.',
                    'The width should be a positive number.',
                    'The height should be a positive number.',
                    'A cell should be either live or dead.',
                ],
                $exception->getErrors()
            );

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

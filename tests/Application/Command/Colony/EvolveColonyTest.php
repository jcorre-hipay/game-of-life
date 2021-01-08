<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\EvolveColonyCommand;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Bus\CommandBusInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EvolveColonyTest extends KernelTestCase
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
        static::bootKernel();

        $this->commandBus = static::$container->get(CommandBusInterface::class);
        $this->repository = static::$container->get(ColonyRepositoryInterface::class);

        $factory = static::$container->get(ColonyFactoryInterface::class);
        $colonyId = $this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->repository->add(
            $factory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );
    }

    protected function tearDown(): void
    {
        try {
            $this->repository->remove($this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));
        } catch (ColonyDoesNotExistException $exception) {
        }
    }

    /**
     * @test
     */
    public function itEvolvesAColonyToTheNextGeneration(): void
    {
        $command = new EvolveColonyCommand('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $events = $this->commandBus->send($command);

        Assert::assertInstanceOf(DomainEventCollection::class, $events);
        Assert::assertCount(4, $events);

        Assert::assertInstanceOf(CellDied::class, $events[0]);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $events[0]->getEntityId()->toString());
        Assert::assertSame(3, $events[0]->getIndex());

        Assert::assertInstanceOf(CellBorn::class, $events[1]);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $events[1]->getEntityId()->toString());
        Assert::assertSame(4, $events[1]->getIndex());

        Assert::assertInstanceOf(CellDied::class, $events[2]);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $events[2]->getEntityId()->toString());
        Assert::assertSame(5, $events[2]->getIndex());

        Assert::assertInstanceOf(GenerationEnded::class, $events[3]);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $events[3]->getEntityId()->toString());
        Assert::assertSame(0, $events[3]->getGeneration());

        $colony = $this->repository->find($this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));

        Assert::assertInstanceOf(ColonyInterface::class, $colony);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $colony->getId()->toString());
        Assert::assertSame(1, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'dead', 'live', 'dead'], $colony->getCellStates());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTheColonyDoesNotExist(): void
    {
        $command = new EvolveColonyCommand('4aea4bdb-c789-4945-8086-54bf22561c27');

        try {
            $this->commandBus->send($command);
        } catch (ColonyNotFoundException $exception) {
            Assert::assertSame('Cannot find colony 4aea4bdb-c789-4945-8086-54bf22561c27.', $exception->getMessage());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenCommandIsNotValid(): void
    {
        $command = new EvolveColonyCommand('1224');

        try {
            $this->commandBus->send($command);
        } catch (InvalidParametersException $exception) {
            Assert::assertSame(['The colony id should follow the uuid format.'], $exception->getErrors());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

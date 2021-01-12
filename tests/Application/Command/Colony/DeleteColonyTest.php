<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Application\Command\Colony;

use GameOfLife\Application\Command\Colony\DeleteColonyCommand;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\ColonyDestroyed;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Bus\CommandBusInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeleteColonyTest extends KernelTestCase
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

        parent::tearDown();
    }

    /**
     * @test
     */
    public function itDeletesAColony(): void
    {
        $command = new DeleteColonyCommand('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $events = $this->commandBus->send($command);

        Assert::assertInstanceOf(DomainEventCollection::class, $events);
        Assert::assertCount(1, $events);

        /** @var ColonyDestroyed $event */
        $event = \current($events);

        Assert::assertInstanceOf(ColonyDestroyed::class, $event);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $event->getEntityId()->toString());

        $colony = $this->repository->find($this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));

        Assert::assertNull($colony);
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTheColonyDoesNotExist(): void
    {
        $command = new DeleteColonyCommand('4aea4bdb-c789-4945-8086-54bf22561c27');

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
        $command = new DeleteColonyCommand('1224');

        try {
            $this->commandBus->send($command);
        } catch (InvalidParametersException $exception) {
            Assert::assertSame(['The colony id should follow the uuid format.'], $exception->getErrors());

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

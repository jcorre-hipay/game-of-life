<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Application\Query\Colony;

use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColonyQuery;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Bus\QueryBusInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetColonyTest extends KernelTestCase
{
    /**
     * @var QueryBusInterface
     */
    private $queryBus;

    /**
     * @var ColonyRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->queryBus = static::$container->get(QueryBusInterface::class);
        $this->repository = static::$container->get(ColonyRepositoryInterface::class);

        $factory = static::$container->get(ColonyFactoryInterface::class);
        $evolveCell = static::$container->get(EvolveCellInterface::class);

        $colonyId = $this->repository->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');
        $colony = $factory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live']);

        $this->repository->add($colony);

        for ($generation = 0; $generation < 2; $generation++) {
            $events = $colony->evolve($evolveCell);
            $colony = $colony->apply($events);
            $this->repository->commit($events);
        }
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
    public function itFindsAColonyAtItsLastGeneration(): void
    {
        $query = new GetColonyQuery('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $result = $this->queryBus->send($query);

        Assert::assertInstanceOf(ColonyResult::class, $result);
        Assert::assertCount(1, $result);

        /** @var Colony $colony */
        $colony = \current($result);

        Assert::assertInstanceOf(Colony::class, $colony);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $colony->getId());
        Assert::assertSame(2, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'dead', 'dead', 'dead', 'dead', 'dead'], $colony->getCellStates());
    }

    /**
     * @test
     * @dataProvider provideFindsColonyAtAGeneration
     */
    public function itFindsAColonyAtAGeneration(int $generation, array $expectedCellStates): void
    {
        $query = new GetColonyQuery('59494a9a-32cc-481e-a4f1-093a8dcef162', $generation);

        $result = $this->queryBus->send($query);

        Assert::assertInstanceOf(ColonyResult::class, $result);
        Assert::assertCount(1, $result);

        /** @var Colony $colony */
        $colony = \current($result);

        Assert::assertInstanceOf(Colony::class, $colony);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $colony->getId());
        Assert::assertSame($generation, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame($expectedCellStates, $colony->getCellStates());
    }

    public function provideFindsColonyAtAGeneration(): array
    {
        return [
            [0, ['dead', 'live', 'dead', 'live', 'dead', 'live']],
            [1, ['dead', 'live', 'dead', 'dead', 'live', 'dead']],
            [2, ['dead', 'dead', 'dead', 'dead', 'dead', 'dead']],
        ];
    }

    /**
     * @test
     */
    public function itReturnsAnEmptyResultWhenTheColonyDoesNotExist(): void
    {
        $query = new GetColonyQuery('4aea4bdb-c789-4945-8086-54bf22561c27');

        $result = $this->queryBus->send($query);

        Assert::assertInstanceOf(ColonyResult::class, $result);
        Assert::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itReturnsAnEmptyResultWhenTheColonyDoesNotExistAtTheRequestedGeneration(): void
    {
        $query = new GetColonyQuery('59494a9a-32cc-481e-a4f1-093a8dcef162', 3);

        $result = $this->queryBus->send($query);

        Assert::assertInstanceOf(ColonyResult::class, $result);
        Assert::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTheQueryIsNotValid(): void
    {
        $command = new GetColonyQuery('1224', -1);

        try {
            $this->queryBus->send($command);
        } catch (InvalidParametersException $exception) {
            Assert::assertSame(
                [
                    'The colony id should follow the uuid format.',
                    'The generation should be a positive or nul number.',
                ],
                $exception->getErrors()
            );

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

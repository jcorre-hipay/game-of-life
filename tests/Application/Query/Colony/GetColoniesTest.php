<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Application\Query\Colony;

use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColoniesQuery;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Colony\EvolveCellInterface;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Bus\QueryBusInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetColoniesTest extends KernelTestCase
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
        parent::setUp();
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

        $colonyId = $this->repository->getIdFromString('4aea4bdb-c789-4945-8086-54bf22561c27');
        $colony = $factory->create($colonyId, 2, 2, ['dead', 'live', 'live', 'live']);

        $this->repository->add($colony);
    }

    protected function tearDown(): void
    {
        $colonies = [
            '59494a9a-32cc-481e-a4f1-093a8dcef162',
            '4aea4bdb-c789-4945-8086-54bf22561c27',
        ];

        foreach ($colonies as $id) {
            try {
                $this->repository->remove($this->repository->getIdFromString($id));
            } catch (ColonyDoesNotExistException $exception) {
            }
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function itFindsAllColoniesAtTheirLastGeneration(): void
    {
        $result = $this->queryBus->send(new GetColoniesQuery());

        Assert::assertInstanceOf(ColonyResult::class, $result);
        Assert::assertCount(2, $result);

        Assert::assertInstanceOf(Colony::class, $result[0]);
        Assert::assertSame('59494a9a-32cc-481e-a4f1-093a8dcef162', $result[0]->getId());
        Assert::assertSame(2, $result[0]->getGeneration());
        Assert::assertSame(2, $result[0]->getLastGeneration());
        Assert::assertSame(3, $result[0]->getWidth());
        Assert::assertSame(2, $result[0]->getHeight());
        Assert::assertSame(['dead', 'dead', 'dead', 'dead', 'dead', 'dead'], $result[0]->getCellStates());

        Assert::assertInstanceOf(Colony::class, $result[1]);
        Assert::assertSame('4aea4bdb-c789-4945-8086-54bf22561c27', $result[1]->getId());
        Assert::assertSame(0, $result[1]->getGeneration());
        Assert::assertSame(0, $result[1]->getLastGeneration());
        Assert::assertSame(2, $result[1]->getWidth());
        Assert::assertSame(2, $result[1]->getHeight());
        Assert::assertSame(['dead', 'live', 'live', 'live'], $result[1]->getCellStates());
    }
}

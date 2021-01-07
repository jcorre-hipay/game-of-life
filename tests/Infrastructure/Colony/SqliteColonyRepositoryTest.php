<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Infrastructure\Colony;

use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Event\CellBorn;
use GameOfLife\Domain\Event\CellDied;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Domain\Exception\ColonyAlreadyExistsException;
use GameOfLife\Domain\Exception\ColonyDoesNotExistException;
use GameOfLife\Infrastructure\Colony\SqliteColonyRepository;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SqliteColonyRepositoryTest extends KernelTestCase
{
    /**
     * @var SqliteColonyRepository
     */
    private $subject;

    /**
     * @var ColonyFactoryInterface
     */
    private $colonyFactory;

    /**
     * @var \PDO
     */
    private $connection;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->subject = static::$container->get(SqliteColonyRepository::class);
        $this->colonyFactory = static::$container->get(ColonyFactoryInterface::class);
        $this->connection = new \PDO(static::$container->getParameter('database_url'));
    }

    protected function tearDown(): void
    {
        $this
            ->connection
            ->prepare('DELETE FROM colonies WHERE id = :id')
            ->execute(['id' => '59494a9a-32cc-481e-a4f1-093a8dcef162']);

        $this
            ->connection
            ->prepare('DELETE FROM events WHERE entity_id = :id')
            ->execute(['id' => '59494a9a-32cc-481e-a4f1-093a8dcef162']);
    }

    /**
     * @test
     */
    public function itFindsAColonyWhichWasPreviouslyAdded(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $colonyCreated = $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        Assert::assertTrue($colonyId->equals($colonyCreated->getEntityId()), $colonyCreated->getEntityId()->toString());
        Assert::assertSame(0, $colonyCreated->getGeneration());
        Assert::assertSame(3, $colonyCreated->getWidth());
        Assert::assertSame(2, $colonyCreated->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $colonyCreated->getCellStates());

        $colony = $this->subject->find($colonyId);

        Assert::assertTrue($colonyId->equals($colony->getId()), $colony->getId()->toString());
        Assert::assertSame(0, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $colony->getCellStates());
    }

    /**
     * @test
     */
    public function itReturnsNullWhenTheColonyDoesNotExist(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        Assert::assertNull($this->subject->find($colonyId), $colonyId->toString());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenAddingAColonyThatAlreadyExists(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        try {
            $this->subject->add(
                $this->colonyFactory->create($colonyId, 2, 2, ['live', 'live', 'live', 'live'])
            );
        } catch (ColonyAlreadyExistsException $exception) {
            Assert::assertSame(
                'Cannot add colony 59494a9a-32cc-481e-a4f1-093a8dcef162 because it already exists.',
                $exception->getMessage()
            );

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }

    /**
     * @test
     */
    public function itFindsAllColoniesPreviouslyAdded(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        $colonies = $this->subject->findAll();

        Assert::assertCount(1, $colonies);

        Assert::assertTrue($colonyId->equals($colonies[0]->getId()), $colonies[0]->getId()->toString());
        Assert::assertSame(0, $colonies[0]->getGeneration());
        Assert::assertSame(3, $colonies[0]->getWidth());
        Assert::assertSame(2, $colonies[0]->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $colonies[0]->getCellStates());
    }

    /**
     * @test
     */
    public function itUpdatesAColonyByCommittingDomainEvents(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        $this->subject->commit(
            [
                new CellDied($colonyId, new \DateTime(), 3),
                new CellBorn($colonyId, new \DateTime(), 4),
                new CellDied($colonyId, new \DateTime(), 5),
                new GenerationEnded($colonyId, new \DateTime(), 0),
            ]
        );

        $colony = $this->subject->find($colonyId);

        Assert::assertTrue($colonyId->equals($colony->getId()), $colony->getId()->toString());
        Assert::assertSame(1, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'dead', 'live', 'dead'], $colony->getCellStates());
    }

    /**
     * @test
     */
    public function itFindsAColonyAtASpecificGeneration(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        $this->subject->commit(
            [
                new CellDied($colonyId, new \DateTime(), 3),
                new CellBorn($colonyId, new \DateTime(), 4),
                new CellDied($colonyId, new \DateTime(), 5),
                new GenerationEnded($colonyId, new \DateTime(), 0),
                new CellDied($colonyId, new \DateTime(), 1),
                new CellDied($colonyId, new \DateTime(), 4),
                new GenerationEnded($colonyId, new \DateTime(), 1),
            ]
        );

        $colony = $this->subject->find($colonyId, 0);

        Assert::assertTrue($colonyId->equals($colony->getId()), $colony->getId()->toString());
        Assert::assertSame(0, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'live', 'dead', 'live'], $colony->getCellStates());

        $colony = $this->subject->find($colonyId, 1);

        Assert::assertTrue($colonyId->equals($colony->getId()), $colony->getId()->toString());
        Assert::assertSame(1, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'live', 'dead', 'dead', 'live', 'dead'], $colony->getCellStates());

        $colony = $this->subject->find($colonyId, 2);

        Assert::assertTrue($colonyId->equals($colony->getId()), $colony->getId()->toString());
        Assert::assertSame(2, $colony->getGeneration());
        Assert::assertSame(3, $colony->getWidth());
        Assert::assertSame(2, $colony->getHeight());
        Assert::assertSame(['dead', 'dead', 'dead', 'dead', 'dead', 'dead'], $colony->getCellStates());

        Assert::assertNull($this->subject->find($colonyId, 3));
        Assert::assertNull($this->subject->find($colonyId, -1));
    }

    /**
     * @test
     */
    public function itRemovesAColony(): void
    {
        $colonyId = $this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162');

        $this->subject->add(
            $this->colonyFactory->create($colonyId, 3, 2, ['dead', 'live', 'dead', 'live', 'dead', 'live'])
        );

        Assert::assertNotNull($this->subject->find($colonyId), $colonyId->toString());

        $this->subject->remove($colonyId);

        Assert::assertNull($this->subject->find($colonyId), $colonyId->toString());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenRemovingAColonyThatDoesNotExist(): void
    {
        try {
            $this->subject->remove($this->subject->getIdFromString('59494a9a-32cc-481e-a4f1-093a8dcef162'));
        } catch (ColonyDoesNotExistException $exception) {
            Assert::assertSame(
                'Cannot remove colony 59494a9a-32cc-481e-a4f1-093a8dcef162 because it does not exist.',
                $exception->getMessage()
            );

            return;
        }

        Assert::fail('Fail asserting an exception has been thrown.');
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Domain\Event\GenerationEnded;
use GameOfLife\Tests\Behat\ColonyHistory\Builder;
use GameOfLife\Tests\Behat\ColonyHistory\Comparator;
use GameOfLife\Tests\Behat\ColonyHistory\Parser;
use GameOfLife\Tests\Mock\Infrastructure\Colony\GeneratePredictableCellState;
use GameOfLife\Tests\Mock\Infrastructure\Identifier\GeneratePredictableEntityIdSeed;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    private $session;
    private $parser;
    private $comparator;
    private $colonyFactory;
    private $colonyRepository;
    private $uuidGenerator;
    private $cellStateGenerator;

    public function __construct(
        Session $session,
        Parser $parser,
        Comparator $comparator,
        ColonyFactoryInterface $colonyFactory,
        ColonyRepositoryInterface $colonyRepository,
        GeneratePredictableEntityIdSeed $uuidGenerator,
        GeneratePredictableCellState $cellStateGenerator
    ) {
        $this->session = $session;
        $this->parser = $parser;
        $this->comparator = $comparator;
        $this->colonyFactory = $colonyFactory;
        $this->colonyRepository = $colonyRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->cellStateGenerator = $cellStateGenerator;
    }

    /**
     * @AfterScenario
     */
    public function after()
    {
        foreach ($this->colonyRepository->findAll() as $colony) {
            $this->colonyRepository->remove($colony->getId());
        }
    }

    /**
     * @Given /^there is the colony "([^"]+)":$/
     */
    public function thereIsTheColony(string $colonyId, PyStringNode $colony): void
    {
        $colonyHistory = $this->parser->execute(new Builder(), $colony->getRaw());

        if (empty($colonyHistory)) {
            return;
        }

        $colony = \array_shift($colonyHistory);

        $id = $this->colonyRepository->getIdFromString($colonyId);
        $this->colonyRepository->add(
            $this->colonyFactory->create($id, $colony['width'], $colony['height'], $colony['cell_states'])
        );

        while (!empty($colonyHistory)) {
            $next = \array_shift($colonyHistory);

            $now = new \DateTimeImmutable();
            $events = $this->comparator->execute($id, $now, $colony['cell_states'], $next['cell_states']);
            $events[] = new GenerationEnded($id, $now, $colony['generation']);
            $this->colonyRepository->commit($events);

            $colony = $next;
        }
    }

    /**
     * @Given /^the next generated UUID will be "([^"]+)"$/
     */
    public function theNextGeneratedUUIDWillBe(string $uuid): void
    {
        $this->uuidGenerator->set([$uuid]);
    }

    /**
     * @Given /^the next generated colony will be:$/
     */
    public function theNextGeneratedColonyWillBe(PyStringNode $colony): void
    {
        $colonyHistory = $this->parser->execute(new Builder(), $colony->getRaw());

        $sequence = [];
        foreach ($colonyHistory as $snapshot) {
            $sequence = \array_merge($sequence, $snapshot['cell_states']);
        }

        $this->cellStateGenerator->set($sequence);
    }

    /**
     * @Then /^I should see the colony "([^"]+)" at generation ([0-9]+):$/
     */
    public function iShouldSeeTheColonyAtGeneration(string $colonyId, string $generation, PyStringNode $colony): void
    {
        $expectedColonyHistory = $this->parser->execute(new Builder(), $colony->getRaw());
        Assert::assertCount(1, $expectedColonyHistory, 'The PyString should represent exactly one colony.');

        $expectedColony = $expectedColonyHistory[0];
        $expectedColony['generation'] = $generation;
        $expectedColony['id'] = $colonyId;

        $page = $this->session->getPage();

        $colonyIdNode = $page->find('css', 'h1');
        Assert::assertInstanceOf(NodeElement::class, $colonyIdNode, 'Node not found: h1');

        $colonyGenerationNode = $page->find('css', '#colony-generation');
        Assert::assertInstanceOf(NodeElement::class, $colonyGenerationNode, 'Node not found: #colony-generation');

        $colonyNode = $page->find('css', '#colony');
        Assert::assertInstanceOf(NodeElement::class, $colonyNode, 'Node not found: #colony');

        $actualColony = [
            'cell_states' => [],
            'generation' => $colonyGenerationNode->getText(),
            'height' => \count($colonyNode->findAll('css', 'tr')),
            'width' => \count($colonyNode->findAll('css', 'tr:first-child td')),
            'id' => \preg_replace('/^Colony /', '', $colonyIdNode->getText()),
        ];

        foreach ($colonyNode->findAll('css', 'td') as $cellNode) {
            $actualColony['cell_states'][] = $cellNode->hasClass('live-cell') ? 'live': 'dead';
        }

        Assert::assertSame($expectedColony, $actualColony);
    }
}

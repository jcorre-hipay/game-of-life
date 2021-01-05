<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use GameOfLife\Domain\Colony\ColonyFactoryInterface;
use GameOfLife\Domain\Colony\ColonyRepositoryInterface;
use GameOfLife\Tests\Behat\ColonyHistory\Builder;
use GameOfLife\Tests\Behat\ColonyHistory\Parser;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    private $session;
    private $parser;
    private $colonyFactory;
    private $colonyRepository;

    public function __construct(
        Session $session,
        Parser $parser,
        ColonyFactoryInterface $colonyFactory,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->session = $session;
        $this->parser = $parser;
        $this->colonyFactory = $colonyFactory;
        $this->colonyRepository = $colonyRepository;
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
        Assert::assertCount(1, $colonyHistory, 'The PyString should represent exactly one colony.');

        $this->colonyRepository->add(
            $this->colonyFactory->create(
                $this->colonyRepository->getIdFromString($colonyId),
                $colonyHistory[0]['width'],
                $colonyHistory[0]['height'],
                $colonyHistory[0]['cell_states']
            )
        );
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

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use GameOfLife\Application\Command\Colony\EvolveColonyCommand;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColonyQuery;
use GameOfLife\Infrastructure\Bus\CommandBusInterface;
use GameOfLife\Infrastructure\Bus\QueryBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ColonyController extends AbstractController
{
    private $commandBus;
    private $queryBus;

    /**
     * @param CommandBusInterface $commandBus
     * @param QueryBusInterface $queryBus
     */
    public function __construct(CommandBusInterface $commandBus, QueryBusInterface $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /**
     * @Route("/colony", methods={"GET"}, name="list_colonies")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render(
            'colony/index.html.twig',
            [
                'colonies' => [
                    [
                        'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                        'generation' => 0,
                    ],
                    [
                        'id' => '4aea4bdb-c789-4945-8086-54bf22561c27',
                        'generation' => 34,
                    ],
                    [
                        'id' => '0310477c-8a5d-401e-835f-1e615c9972c0',
                        'generation' => 0,
                    ],
                ],
            ]
        );
    }

    /**
     * @Route("/colony/{id}/{generation}", methods={"GET"}, name="show_colony")
     *
     * @param string $id
     * @param int $generation
     * @return Response
     * @throws ApplicationException
     */
    public function show(string $id, int $generation): Response
    {
        try {
            /** @var ColonyResult $result */
            $result = $this->queryBus->send(new GetColonyQuery($id, $generation));

        } catch (InvalidParametersException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }

        if (0 === \count($result)) {
            throw $this->createNotFoundException();
        }

        /** @var Colony $colony */
        $colony = \current($result);

        return $this->render(
            'colony/show.html.twig',
            [
                'colony' => [
                    'id' => $colony->getId(),
                    'generation' => $colony->getGeneration(),
                    'width' => $colony->getWidth(),
                    'height' => $colony->getHeight(),
                    'cell_states' => $colony->getCellStates(),
                ],
            ]
        );
    }

    /**
     * @Route("/colony/{id}", methods={"POST"}, name="evolve_colony")
     *
     * @param string $id
     * @return Response
     * @throws ApplicationException
     */
    public function evolve(string $id): Response
    {
        try {
            /** @var ColonyResult $result */
            $this->commandBus->send(new EvolveColonyCommand($id));
            $result = $this->queryBus->send(new GetColonyQuery($id));

        } catch (InvalidParametersException|ColonyNotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }

        if (0 === \count($result)) {
            throw $this->createNotFoundException();
        }

        /** @var Colony $colony */
        $colony = \current($result);

        return $this->redirectToRoute(
            'show_colony',
            [
                'id' => $colony->getId(),
                'generation' => $colony->getGeneration(),
            ]
        );
    }
}

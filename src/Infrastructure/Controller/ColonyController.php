<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use GameOfLife\Application\Command\Colony\CreateColonyCommand;
use GameOfLife\Application\Command\Colony\DeleteColonyCommand;
use GameOfLife\Application\Command\Colony\EvolveColonyCommand;
use GameOfLife\Application\Command\DomainEventCollection;
use GameOfLife\Application\Exception\ApplicationException;
use GameOfLife\Application\Exception\ColonyNotFoundException;
use GameOfLife\Application\Exception\InvalidParametersException;
use GameOfLife\Application\Query\Colony\Colony;
use GameOfLife\Application\Query\Colony\ColonyResult;
use GameOfLife\Application\Query\Colony\GetColoniesQuery;
use GameOfLife\Application\Query\Colony\GetColonyQuery;
use GameOfLife\Domain\Event\ColonyCreated;
use GameOfLife\Infrastructure\Bus\CommandBusInterface;
use GameOfLife\Infrastructure\Bus\QueryBusInterface;
use GameOfLife\Infrastructure\Colony\GenerateCellStateInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @throws ApplicationException
     */
    public function index(): Response
    {
        $result = $this->queryBus->send(new GetColoniesQuery());

        $colonies = [];

        foreach ($result as $colony) {
            /** @var Colony $colony */
            $colonies[] = [
                'id' => $colony->getId(),
                'generation' => $colony->getLastGeneration(),
            ];
        }

        return $this->render('colony/index.html.twig', ['colonies' => $colonies]);
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
                'has_next_generation' => $colony->getGeneration() !== $colony->getLastGeneration(),
                'has_previous_generation' => $colony->getGeneration() !== 0,
            ]
        );
    }

    /**
     * @Route("/colony/new", methods={"GET"}, name="new_colony")
     *
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        return $this->render('colony/new.html.twig', ['errors' => $request->query->get('errors', [])]);
    }

    /**
     * @Route("/colony", methods={"POST"}, name="create_colony")
     *
     * @param GenerateCellStateInterface $generator
     * @param Request $request
     * @return Response
     * @throws ApplicationException
     */
    public function create(GenerateCellStateInterface $generator, Request $request): Response
    {
        $width = \intval($request->request->get('width', 0));
        $height = \intval($request->request->get('height', 0));

        $cellStates = [];
        $size = $width * $height;
        for ($index = 0; $index < $size; $index++) {
            $cellStates[] = $generator->execute();
        }

        $redirectionParameters = [];

        try {
            /** @var DomainEventCollection $events */
            $events = $this->commandBus->send(new CreateColonyCommand($width, $height, $cellStates));

            foreach ($events as $event) {
                if (!$event instanceof ColonyCreated) {
                    continue;
                }

                return $this->redirectToRoute(
                    'show_colony',
                    [
                        'id' => $event->getEntityId()->toString(),
                        'generation' => $event->getGeneration(),
                    ]
                );
            }
        } catch (InvalidParametersException $exception) {
            $redirectionParameters['errors'] = $exception->getErrors();
        }

        return $this->redirectToRoute('new_colony', $redirectionParameters);
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

    /**
     * @Route("/colony/{id}/delete", methods={"POST"}, name="delete_colony")
     *
     * @param string $id
     * @return Response
     * @throws ApplicationException
     */
    public function delete(string $id): Response
    {
        try {
            $this->commandBus->send(new DeleteColonyCommand($id));
        } catch (ColonyNotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }

        return $this->redirectToRoute('list_colonies');
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ColonyController extends AbstractController
{
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
                        'generation' => 42,
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
     */
    public function show(string $id, int $generation): Response
    {
        return $this->render(
            'colony/show.html.twig',
            [
                'colony' => [
                    'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                    'generation' => 42,
                ],
            ]
        );
    }

    /**
     * @Route("/colony/{id}", methods={"POST"}, name="evolve_colony")
     *
     * @param string $id
     * @return Response
     */
    public function evolve(string $id): Response
    {
        return $this->redirectToRoute(
            'show_colony',
            [
                'id' => '59494a9a-32cc-481e-a4f1-093a8dcef162',
                'generation' => 42,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController
{
    /**
     * @Route("/", methods={"GET"}, name="home")
     *
     * @return Response
     */
    public function index(): Response
    {
        return new Response("Game of Life is working!", 200, ['content-type' => 'text/plain']);
    }
}

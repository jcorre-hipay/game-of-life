<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"}, name="home")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->redirectToRoute('list_colonies', [], 301);
    }
}

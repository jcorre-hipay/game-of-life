<?php

declare(strict_types=1);

namespace GameOfLife\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $exception = $request->attributes->get('exception');

        if ($exception instanceof HttpException) {
            $template = \sprintf('error/%d.html.twig', $exception->getStatusCode());

            if ($this->get('twig')->getLoader()->exists($template)) {
                return $this->render($template);
            }
        }

        return $this->render('error/500.html.twig');
    }
}

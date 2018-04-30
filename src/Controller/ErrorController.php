<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/p6/erreur", name="error_")
 * Class ErrorController
 * @package App\Controller
 */
class ErrorController extends AppController
{
    /**
     * @Route("/401", name="401")
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function e401(): Response
    {
        return $this->render('error/401.html.twig', []);
    }
}

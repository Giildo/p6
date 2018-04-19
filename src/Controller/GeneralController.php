<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route(name="general_")
 * Class GeneralController
 * @package App\Controller
 */
class GeneralController
{
    /**
     * @Route("/accueil", name="index")
     * @param Environment $twig
     * @param RegistryInterface $doctrine
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(Environment $twig, RegistryInterface $doctrine): Response
    {
        $tricks = $doctrine->getRepository(Trick::class)
            ->findAll();

        return new Response($twig->render('general/index.html.twig', compact('tricks')));
    }

    /**
     * @Route("/", name="base")
     */
    public function base(): RedirectResponse
    {
        return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }
}

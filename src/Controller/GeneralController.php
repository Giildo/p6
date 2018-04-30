<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/p6", name="general_")
 * Class GeneralController
 * @package App\Controller
 */
class GeneralController extends AppController
{
    /**
     * Récupère les figures via doctrine.
     * Crée un tableau vide de jetons. Récupère l'utilisateur connecté dans la session.
     * Utilise une méthode crypter un code pour faire un jeton.
     *
     * @Route("/accueil", name="index")
     * @param RegistryInterface $doctrine
     * @param SessionInterface $session
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(RegistryInterface $doctrine, SessionInterface $session): Response
    {
        $tricks = $doctrine->getRepository(Trick::class)
            ->findAll();

        $tokens = [];
        if ($this->isContrib()) {
            /** @var User $user */
            $user = $session->get('user');

            /** @var Trick $trick */
            $date = new DateTime();
            foreach ($tricks as $trick) {
                if ($trick->getUser()->getId() === $user->getId()) {
                    $tokens[$trick->getId()] = hash(
                        'sha512',
                        $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
                    );
                } else {
                    $tokens[$trick->getId()] = null;
                }
            }
        }

        return $this->render('general/index.html.twig', compact('tricks', 'tokens'));
    }
}

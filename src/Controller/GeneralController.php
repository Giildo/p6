<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function index(RegistryInterface $doctrine): Response
    {
        $tricks = $doctrine->getRepository(Trick::class)
            ->findAll();

        $tokens = [];
        if ($this->statusService->isContrib()) {
            /** @var User $user */
            $user = $this->userService->userConnected();

            /** @var Trick $trick */
            $date = new DateTime();
            foreach ($tricks as $trick) {
                $tokens[$trick->getId()] = null;

                if ($trick->getUser()->getId() === $user->getId()) {
                    $tokens[$trick->getId()] = hash(
                        'sha512',
                        $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
                    );
                }
            }
        }

        return $this->render('general/index.html.twig', compact('tricks', 'tokens'));
    }
}

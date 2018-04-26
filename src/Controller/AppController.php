<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;

abstract class AppController extends Controller
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    protected function isConnected(): bool
    {
        $session = new Session();
        $user = $session->get('user');

        if (!($user instanceof User)) {
            return false;
        }

        $tokenVerif = hash('sha512', $user->getId() . strlen($user->getPseudo()) . $user->getLastName());
        $token = $session->get('time');

        if ($token !== $tokenVerif) {
            return false;
        }

        return true;
    }

    protected function isAdmin(): bool
    {
        if ($this->isConnected()) {
            $session = new Session();

            /** @var User $user */
            $user = $session->get('user');

            return $user->getStatus()->getId() === 3;
        } else {
            return false;
        }
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $view, array $parameters = array(), Response $response = null): Response
    {
        $parameters['isAdmin'] = $this->isAdmin();
        $parameters['isConnected'] = $this->isConnected();

        return new Response($this->twig->render($view, $parameters));
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;

abstract class AppController
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
     * @param string $path
     * @param array|null $values
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $path, ?array $values = [])
    {
        $values['isAdmin'] = $this->isAdmin();
        $values['isConnected'] = $this->isConnected();

        return $this->twig->render($path, $values);
    }

}

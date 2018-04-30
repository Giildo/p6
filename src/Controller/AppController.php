<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * Vérifie si l'utilisateur en Session est une instance de @uses User
     * Vérifie le jeton associé dans la variable "time"
     * Si tout est OK return true
     *
     * @return bool
     */
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

    /**
     * Vérifie que l'utilisateur est connecté avec la méthode @uses AppController::isConnected(),
     * puis vérifie que le dit utilisateur a au moins le @uses Status::$name "Contributeur"
     *
     * @return bool
     */
    protected function isContrib(): bool
    {
        if ($this->isConnected()) {
            $session = new Session();

            /** @var User $user */
            $user = $session->get('user');

            return $user->getStatus()->getId() === 2 || $user->getStatus()->getId() === 3;
        } else {
            return false;
        }
    }

    /**
     * Vérifie que l'utilisateur est connecté avec la méthode @uses AppController::isConnected(),
     * puis vérifie que le dit utilisateur a le @uses Status::$name "Administrateur"
     *
     * @return bool
     */
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
        $parameters['isContrib'] = $this->isContrib();
        $parameters['isConnected'] = $this->isConnected();

        if ($this->isConnected()) {
            $session = new Session();
            $parameters['userConnected'] = $session->get('user');
        }

        return new Response($this->twig->render($view, $parameters));
    }

    /**
     * Fonction qui redirige vers la page d'accueil
     *
     * @return RedirectResponse
     */
    protected function redirectToHome(): RedirectResponse
    {
        return $this->redirectToRoute('general_index', [], RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Fonction qui redirige l'utilisateur vers la bonne page d'erreur.
     *
     * @param int $errorNumber
     * @param array|null $params
     * @return RedirectResponse
     */
    protected function redirectToError(int $errorNumber, ?array $params = []): RedirectResponse
    {
        switch ($errorNumber) {
            case 404:
                return $this->redirectToRoute('error_404', $params, RedirectResponse::HTTP_NOT_FOUND);
                break;

            case 500:
                return $this->redirectToRoute('error_500', $params, RedirectResponse::HTTP_INTERNAL_SERVER_ERROR);
                break;

            case 401:
                return $this->redirectToRoute('error_401', $params, RedirectResponse::HTTP_UNAUTHORIZED);
                break;

            default:
                return $this->redirectToRoute('error_404', $params, RedirectResponse::HTTP_NOT_FOUND);
                break;
        }
    }
}

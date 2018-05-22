<?php

namespace App\Controller;

use App\Services\StatusService;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AppController extends Controller
{
    /**
     * @var StatusService
     */
    protected $statusService;
    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(StatusService $statusService, UserService $userService)
    {
        $this->statusService = $statusService;
        $this->userService = $userService;
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @return Response
     */
    protected function render(string $view, array $parameters = array(), Response $response = null): Response
    {
        $parameters['isAdmin'] = $this->statusService->isAdmin();
        $parameters['isContrib'] = $this->statusService->isContrib();
        $parameters['isConnected'] = $this->statusService->isConnected();

        if ($this->statusService->isConnected()) {
            $parameters['userConnected'] = $this->userService->userConnected();
        }

        return parent::render($view, $parameters, $response);
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
                return $this->redirectToRoute('error_401', $params, RedirectResponse::HTTP_MOVED_PERMANENTLY);
                break;

            default:
                return $this->redirectToRoute('error_404', $params, RedirectResponse::HTTP_MOVED_PERMANENTLY);
                break;
        }
    }
}

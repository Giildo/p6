<?php

namespace App\Services;

use App\Entity\Status;
use App\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StatusService
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(SessionInterface $session, RegistryInterface $doctrine)
    {
        $this->session = $session;
        $this->doctrine = $doctrine;
    }

    /**
     * Vérifie si l'utilisateur en Session est une instance de @uses User
     * Vérifie le jeton associé dans la variable "time"
     * Si tout est OK return true
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        $user = $this->session->get('user');

        if (!($user instanceof User)) {
            return false;
        }

        $tokenVerif = hash('sha512', strlen($user->getPseudo()) . $user->getLastName());
        $token = $this->session->get('time');

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
    public function isContrib(): bool
    {
        if ($this->isConnected()) {
            /** @var User $user */
            $user = $this->session->get('user');

            /** @var Status $status */
            $status = $this->doctrine->getRepository(Status::class)
                ->find($user->getStatus()->getId());

            return $status->getName() === Status::CONTRIB || $status->getName() === Status::ADMIN;
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
    public function isAdmin(): bool
    {
        if ($this->isConnected()) {
            /** @var User $user */
            $user = $this->session->get('user');

            /** @var Status $status */
            $status = $this->doctrine->getRepository(Status::class)
                ->find($user->getStatus()->getId());

            return $status->getName() === Status::ADMIN;
        } else {
            return false;
        }
    }
}

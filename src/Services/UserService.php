<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserService
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return User|null
     */
    public function userConnected(): ?User
    {
        return $this->session->get('user');
    }
}

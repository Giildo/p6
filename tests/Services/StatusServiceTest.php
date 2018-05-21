<?php

namespace tests\Services;

use App\Entity\Status;
use App\Entity\User;
use App\Services\StatusService;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class StatusServiceTest extends TestCase
{
    /**
     * @var Session
     */
    private $session;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->session = new Session(new MockArraySessionStorage());
    }

    /**
     * @expectedException Error
     */
    public function testConstructor()
    {
        new StatusService(new stdClass());
    }

    public function testIsConnectedFalseWithoutUserInSession()
    {
        $status = new StatusService($this->session);

        $this->assertFalse($status->isConnected());
    }

    public function testIsConnectedTrueWithUserInSession()
    {
        $user = (new User())->setPseudo('Giildo')
            ->setLastName('Jonathan');
        $this->session->set('user', $user);

        $token = hash(
            'sha512',
            strlen('Giildo') . 'Jonathan'
        );
        $this->session->set('time', $token);

        $statusService = new StatusService($this->session);

        $this->assertTrue($statusService->isConnected());
    }

    public function testIsConnectedFalseWithBadUserInSession()
    {
        $user = (new User())->setPseudo('Koldo')
            ->setLastName('Régis');
        $this->session->set('user', $user);

        $token = hash(
            'sha512',
            strlen('Giildo') . 'Jonathan'
        );
        $this->session->set('time', $token);

        $statusService = new StatusService($this->session);

        $this->assertFalse($statusService->isConnected());
    }

    public function testIsContribFalseAndAdminFalseWithUserUtilisateurInSession()
    {
        $user = (new User())->setPseudo('Giildo')
            ->setLastName('Jonathan');
        $status = (new Status())->setName('Utilisateur');
        $user->setStatus($status);
        $this->session->set('user', $user);

        $token = hash(
            'sha512',
            strlen('Giildo') . 'Jonathan'
        );
        $this->session->set('time', $token);

        $statusService = new StatusService($this->session);

        $this->assertFalse($statusService->isContrib());
        $this->assertFalse($statusService->isAdmin());
    }

    public function testIsContribTrueAndAdminFalseWithUserContributeurInSession()
    {
        $user = (new User())->setPseudo('Giildo')
            ->setLastName('Jonathan');
        $status = (new Status())->setName('Contributeur');
        $user->setStatus($status);
        $this->session->set('user', $user);

        $token = hash(
            'sha512',
            strlen('Giildo') . 'Jonathan'
        );
        $this->session->set('time', $token);

        $statusService = new StatusService($this->session);

        $this->assertTrue($statusService->isContrib());
        $this->assertFalse($statusService->isAdmin());
    }

    public function testIsContribTrueAndAdminTrueWithUserAdministrateurInSession()
    {
        $user = (new User())->setPseudo('Giildo')
            ->setLastName('Jonathan');
        $status = (new Status())->setName('Administrateur');
        $user->setStatus($status);
        $this->session->set('user', $user);

        $token = hash(
            'sha512',
            strlen('Giildo') . 'Jonathan'
        );
        $this->session->set('time', $token);

        $statusService = new StatusService($this->session);

        $this->assertTrue($statusService->isContrib());
        $this->assertTrue($statusService->isAdmin());
    }
}

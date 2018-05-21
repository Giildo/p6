<?php

namespace tests\Services;

use App\Entity\User;
use App\Services\UserService;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserServiceTest extends TestCase
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
        new UserService(new StdClass());
    }

    public function testUserRecovering()
    {
        $user = (new User())->setPseudo('Giildo')
            ->setLastName('Marco')
            ->setFirstName('Jonathan');

        $this->session->set('user', $user);

        $userService = new UserService($this->session);

        $userConnected = $userService->userConnected();

        $this->assertEquals($user, $userConnected);
    }
}

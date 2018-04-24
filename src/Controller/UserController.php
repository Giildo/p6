<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route(name="user_")
 * Class UserController
 * @package App\Controller
 */
class UserController extends AppController
{
    /**
     * @Route("/connexion", name="connection")
     * @param FormFactoryInterface $formFactory
     * @param Environment $twig
     * @param RegistryInterface $doctrine
     * @param SessionInterface $session
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function connection(FormFactoryInterface $formFactory, Environment $twig, RegistryInterface $doctrine, SessionInterface $session)
    {
        if (!$this->isConnected()) {
            if (isset($_POST)) {
                if (isset($_POST['form']['c_pseudo']) && isset($_POST['form']['c_mdp'])) {
                    $user = $this->userVerif($doctrine);

                    if (!is_null($user)) {
                        $this->connectUser($session, $user);
                        return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
                    }
                } elseif (
                    isset($_POST['form']['pseudo']) &&
                    isset($_POST['form']['password']) &&
                    isset($_POST['form']['passwordVerif']) &&
                    isset($_POST['form']['firstName']) &&
                    isset($_POST['form']['lastName']) &&
                    isset($_POST['form']['mail'])
                ) {
                    $this->registryUser($doctrine);
                }
            }

            $formConnection = $formFactory->createBuilder()
                ->add('c_pseudo', TextType::class, ['label' => 'Pseudo'])
                ->add('c_mdp', PasswordType::class, ['label' => 'Mot de passe'])
                ->getForm();

            $formRegistry = $formFactory->createBuilder()
                ->add('pseudo', TextType::class)
                ->add('password', PasswordType::class)
                ->add('passwordVerif', PasswordType::class)
                ->add('firstName', TextType::class)
                ->add('lastName', TextType::class)
                ->add('mail', EmailType::class)
                ->add('phone', NumberType::class)
                ->getForm();

            return new Response($this->render('user/connection.html.twig', [
                'formConnection' => $formConnection->createView(),
                'formRegistry'   => $formRegistry->createView()
            ]));
        } else {
            return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
        }
    }

    /**
     * @Route("/deconnexion", name="disconnection")
     */
    public function disconnection(SessionInterface $session): RedirectResponse
    {
        $session->remove('user');
        $session->remove('time');

        return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/admin/utilisateurs", name="admin_users")
     */
    public function adminUsers()
    {
    }

    /**
     * @param RegistryInterface $doctrine
     * @return User|null
     */
    private function userVerif(RegistryInterface $doctrine): ?User
    {
        /** @var User $user */
        $user = $doctrine->getRepository(User::class)
            ->findOneBy(['pseudo' => $_POST['form']['c_pseudo']]);

        if (!$user->getValid()) {
            return null;
        }

        return $user;
    }

    /**
     * @param SessionInterface $session
     * @param User|null $user
     * @return bool
     */
    private function connectUser(SessionInterface $session, ?User $user = null): bool
    {
        if (!is_null($user)) {
            $session->set('user', $user);

            $token = hash('sha512', $user->getId() . strlen($user->getPseudo()) . $user->getLastName());
            $session->set('time', $token);

            return true;
        } else {
            return false;
        }
    }

    private function registryUser(RegistryInterface $doctrine): bool
    {
        if ($_POST['form']['password'] === $_POST['form']['passwordVerif']) {
            $user = new User();

            $password = hash('sha512', $_POST['form']['pseudo']);

            /** @var Status $status */
            $status = $doctrine->getRepository(Status::class)
                ->findOneBy(['name' => 'utilisateur']);

            $user->setPseudo($_POST['form']['pseudo'])
                ->setPassword($password)
                ->setFirstName($_POST['form']['firstName'])
                ->setLastName($_POST['form']['lastName'])
                ->setMail($_POST['form']['mail'])
                ->setPhone($_POST['form']['phone'])
                ->setValid(0)
                ->setStatus($status);

            $userManager = $doctrine->getManager();
            $userManager->persist($user);
            $userManager->flush();
            return true;
        } else {
            return false;
        }
    }
}

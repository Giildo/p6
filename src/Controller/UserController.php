<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use App\Exception\UserException;
use App\Form\Type\UserType;
use App\Services\StatusService;
use App\Services\UserService;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gère la connexion, l'enregistrement d'un nouvel utilisateur et la déconnexion.
 *
 * @Route("/p6", name="user_")
 * Class UserController
 * @package App\Controller
 */
class UserController extends AppController
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        StatusService $statusService,
        UserService $userService,
        RegistryInterface $doctrine,
        SessionInterface $session
    ) {
        parent::__construct($statusService, $userService);

        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    /**
     * Vérifie si on vient de la page "enregistrement" qui laisse dans la Session la version de l'utilisateur qui vient
     * de créer un compte. Si ce n'est pas la cas, crée un nouvel utilisateur.
     * Récupère le formulaire de connexion @uses UserType et lui associe l'utilisateur ci-dessus.
     * Attache le formulaire à la requête pour vérifier s'il a été validé et envoyé en POST.
     * Si c'est le cas,
     * @uses UserController::userVerif(). S'il y a une erreur ajoute l'erreur récupérée dans le formulaire. Puis,
     * @uses UserController::connectUser(), pour connecter l'utilisateur et le renvoyer vers l'accueil.
     *
     * @Route("/connexion", name="connection")
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function connection(Request $request)
    {
        if (!$this->statusService->isConnected()) {
            $user = new User();

            if ($this->session->has('userTransfert')) {
                $user = $this->session->get('userTransfert');
                $this->session->remove('userTransfert');
            }

            $form = $this->createForm(UserType::class, $user)
                ->remove('firstName')
                ->remove('lastName')
                ->remove('mail')
                ->remove('phone')
                ->remove('mailValidate')
                ->remove('status')
                ->remove('picture');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $userVerif = null;

                try {
                    $userVerif = $this->userVerif($user);
                } catch (UserException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }

                if (!is_null($userVerif)) {
                    $this->connectUser($userVerif);
                    return $this->redirectToHome();
                }
            }

            return $this->render('user/connection.html.twig', [
                'form'      => $form->createView(),
                'pageTitle' => 'Se connecter',
                'pageType'  => 'connection'
            ]);
        }

        return $this->redirectToHome();
    }

    /**
     * Crée un formulaire, à partir de la classe @uses UserType, avec un utilisateur vierge.
     * Attache le formulaire à la requête pour vérifier si on est sur une page avec la méthode POST.
     * Vérifie si le formulaire a été soumis et est valide.
     * Si c'est le cas,
     * @uses UserController::registryUser(), pour enregistrer l'utilisateur crée grâce au formulaire au niveau de la BDD
     * S'il n'y a pas d'erreurs renvoyées par la méthode, il crée un variable dans la SESSION et y accroche
     * l'utilisateur nouvellement crée et ajoute un message en flash pour indiquer qu'il faut se connecter.
     * Renvoie vers la page de connexion.
     *
     * @Route("/enregistrement", name="registry")
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function registry(Request $request)
    {
        if (!$this->statusService->isConnected()) {
            $user = new User();

            $form = $this->createForm(UserType::class, $user)
                ->remove('mailValidate')
                ->remove('status')
                ->remove('picture');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $verif = false;

                try {
                    $verif = $this->registryUser($user);
                } catch (UserException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }

                if ($verif) {
                    $this->session->set('userTransfert', $user);
                    $this->addFlash(
                        'message',
                        'Après avoir validé votre adresse mail, veuillez vous connecter.'
                    );
                    return $this->redirectToRoute('user_connection');
                }
            }

            return $this->render('user/connection.html.twig', [
                'form'      => $form->createView(),
                'pageTitle' => "S'enregistrer",
                'pageType'  => 'registry'
            ]);
        } else {
            return $this->redirectToHome();
        }
    }

    /**
     * Supprime l'utilisateur en SESSION et la variable 'time' pour déconnecter l'utilisateur.
     *
     * @Route("/deconnexion", name="disconnection")
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function disconnection(SessionInterface $session): RedirectResponse
    {
        $session->remove('user');
        $session->remove('time');

        return $this->redirectToHome();
    }

    /**
     * Vérifie que l'utilisateur qui essaye d'accéder à la page est administrateur.
     * Si c'est le cas, récupère tous les utilisateurs et les envoie à la vue.
     *
     * @Route("/admin/utilisateurs", name="admin_users")
     * @return Response|RedirectResponse
     */
    public function adminUsers()
    {
        if ($this->statusService->isAdmin()) {
            $users = $this->doctrine->getRepository(User::class)
                ->findAll();

            $date = new DateTime();
            /** @var User $user */
            $tokens = [];
            foreach ($users as $user) {
                $tokens[$user->getPseudo()] = hash(
                    'sha512',
                    $user->getPseudo() . $date->format('m') . $user->getLastName() . $date->format('d')
                );
            }
            return $this->render('/admin/users.html.twig', compact('users', 'tokens'));
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * Vérifie que l'utilisateur qui accède à la page est administrateur.
     * Crée un nouvel utilisateur vierge, l'attache à un nouveau formulaire @uses UserType.
     * Vérifie si le formulaire est soumis et valide. Si c'est le cas, ajoute par défaut comme mot de passe le pseudo,
     * et met par défaut l'email en non validé.
     * Ajoute le nouvel utilisateur crée en BDD. Si tout est OK redirige vers la page d'admin.
     *
     * @Route("/admin/utilisateur/ajouter", name="admin_add")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function add(Request $request)
    {
        if ($this->statusService->isAdmin()) {
            $user = new User();

            $form = $this->createForm(UserType::class, $user);
            $form->remove('password')
                ->remove('mailValidate')
                ->remove('picture');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = hash('sha512', $user->getPseudo());
                $user->setPassword($password)
                    ->setMailValidate(false);

                $manager = $this->doctrine->getManager();
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('user_admin_users');
            }

            return $this->render('/admin/modifyAdd_User.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * Vérifie que l'utilisateur qui essaye d'accéder à la page est administrateur.
     * Récupère l'utilisateur à modifier, s'il n'existe pas redirige la page. Crée un formulaire @uses UserType.
     * Attache le formulaire à la requête, vérifie s'il a été soumis et s'il est valide.
     * Si c'est le cas, récupère le manager pour sauvegarder l'utilisateur modifié en BDD.
     *
     * @Route("/admin/utilisateur/modifier/{pseudo}", name="admin_modify", requirements={"pseudo"="\w+"})
     * @param Request $request
     * @param string $pseudo
     * @return RedirectResponse|Response
     */
    public function modify(Request $request, string $pseudo)
    {
        if ($this->statusService->isAdmin()) {
            /** @var User $user */
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['pseudo' => $pseudo]);

            if (is_null($user)) {
                return $this->redirectToRoute('user_admin_users');
            }

            $date = new DateTime();
            $token = hash(
                'sha512',
                $user->getPseudo() . $date->format('m') . $user->getLastName() . $date->format('d')
            );

            if ($request->request->get('token') !== $token && is_null($request->request->get('user'))) {
                return $this->redirectToRoute('user_admin_users');
            }

            $form = $this->createForm(UserType::class, $user);
            $form->remove('password')
                ->add('password', HiddenType::class)
                ->remove('mailValidate')
                ->add('mailValidate', HiddenType::class)
                ->remove('picture');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager = $this->doctrine->getManager();
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('user_admin_users');
            }

            return $this->render('/admin/modifyAdd_User.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * Vérifie que l'utilisateur qui accède à la page est bien administrateur.
     * Récupère l'utilisateur dans la base de donnée avec son pseudo, s'il n'existe pas redirige la page.
     * Vérifie que le token reçu est le bon.
     * Si tout est OK, il supprime l'utilisateur de la BDD via doctrine, puis redirige vers la page d'admin.
     *
     * @Route("/admin/utilisateur/supprimer/{pseudo}", name="admin_delete", requirements={"pseudo"="\w+"})
     * @param Request $request
     * @param string $pseudo
     * @return RedirectResponse
     */
    public function delete(Request $request, string $pseudo): RedirectResponse
    {
        if ($this->statusService->isAdmin()) {
            /** @var User $user */
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['pseudo' => $pseudo]);

            if (is_null($user)) {
                return $this->redirectToRoute('user_admin_users');
            }

            $date = new DateTime();
            $token = hash(
                'sha512',
                $user->getPseudo() . $date->format('m') . $user->getLastName() . $date->format('d')
            );

            if ($request->request->get('token') !== $token) {
                return $this->redirectToRoute('user_admin_users');
            }

            $manager = $this->doctrine->getManager();
            $manager->remove($user);
            $manager->flush();

            return $this->redirectToRoute('user_admin_users');
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * @Route("/profil/{pseudo}", name="profil_index", requirements={"pseudo"="\w+"})
     * @param string $pseudo
     * @return RedirectResponse|Response
     */
    public function profilIndex(string $pseudo)
    {
        if ($this->statusService->isConnected()) {
            /** @var User $user */
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['pseudo' => $pseudo]);

            if (!is_null($user)) {
                return $this->render('/user/profil_index.html.twig', compact('user'));
            } else {
                return $this->redirectToHome();
            }
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * @Route("/profil/modifier/{pseudo}", name="profil_modify", requirements={"pseudo"="\w+"})
     * @param Request $request
     * @param string $pseudo
     * @return Response|RedirectResponse
     */
    public function profilModify(Request $request, string $pseudo)
    {
        if ($this->statusService->isConnected()) {
            /** @var User $user */
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['pseudo' => $pseudo]);

            $originPicture = $user->getPicture();

            if (!is_null($user)) {
                $date = new DateTime();
                $userConnected = $this->userService->userConnected();

                //Vérifie que l'utilisateur connecté est le même que celui du profil
                if ($userConnected->getPseudo() === $user->getPseudo()) {
                    $form = $this->createForm(UserType::class, $user);
                    $form->remove('mailValidate')
                        ->remove('status');

                    //Prend en charge les modifications du profil et le charge en BDD
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $password = hash('sha512', strlen($user->getPassword()) . $user->getPassword());
                        $user->setPassword($password);

                        if (!is_null($originPicture)) {
                            $filePath =
                                "{$originPicture->getUploadRootDir('users')}/
                                {$originPicture->getName()}.
                                {$originPicture->getExt()}";

                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }

                        $picture = $user->getPicture();
                        $picture->setName($user->getPseudo() . (new DateTime())->format('YmdHis'))
                            ->setAlt('Photo de profil de ' . $user->getPseudo())
                            ->upload('users');

                        $manager = $this->doctrine->getManager();
                        $manager->persist($picture);
                        $manager->persist($user);
                        $manager->flush();

                        return $this->redirectToRoute('user_profil_index', [
                            'pseudo' => $user->getPseudo()
                        ]);
                    }

                    //Crée un Token pour la suppression de l'image de profil
                    $picToken = null;
                    if (!is_null($user->getPicture())) {
                        $picToken = hash(
                            'sha512',
                            $user->getPseudo() . $date->format('d') . $user->getPicture()->getAlt()
                        );

                        if (isset($request->request->get('picture')['delete'])) {
                            if ($request->request->get('picture')['delete'] === $picToken) {
                                $filePath = dirname(__DIR__, 2) .
                                    '/public/img/pic_dl/users/' .
                                    $user->getPicture()->getName() .
                                    '.' .
                                    $user->getPicture()->getExt();

                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }

                                $manager = $this->doctrine->getManager();
                                $picture = $user->getPicture();
                                $manager->remove($picture);
                                $user->setPicture(null);
                                $manager->persist($user);
                                $manager->flush();
                            }
                        }
                    }

                    return $this->render('/user/profil_modify.html.twig', [
                        'user'     => $user,
                        'form'     => $form->createView(),
                        'picToken' => $picToken
                    ]);
                } else {
                    return $this->redirectToHome();
                }
            } else {
                return $this->redirectToHome();
            }
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * Récupère le pseudo passé avec la méthode POST.
     * Récupère, via l'ORM, l'utilisateur correspondant au pseudo.
     * S'il n'y a pas d'utilisateur correspondant, s'il n'est pas validé par un admin ou s'il n'a pas validé son email,
     * renvoie une UserException.
     * Vérfie les mots de passe. S'ils ne sont pas identiques, renvoie une UserException.
     * Si tout est OK, renvoie true.
     *
     * @param User $userConnect
     * @return User
     * @throws UserException
     */
    private function userVerif(User $userConnect): User
    {
        /** @var User $userVerif */
        $userVerif = $this->doctrine->getRepository(User::class)
            ->findOneBy(['pseudo' => $userConnect->getPseudo()]);

        if (is_null($userVerif)) {
            throw new UserException(
                "Le pseudo envoyé n'existe pas.",
                UserException::BAD_PSEUDO
            );
        }

        $passVerif = hash('sha512', strlen($userConnect->getPassword()) . $userConnect->getPassword());
        if ($userVerif->getPassword() !== $passVerif) {
            throw new UserException(
                "Le mot de passe est incorrect.",
                UserException::BAD_PASSWORD
            );
        }

        if (!$userVerif->getMailValidate()) {
            throw new UserException(
                "Vous devez valider votre adresse mail.",
                UserException::MAIL_NO_VALIDATED
            );
        }

        return $userVerif;
    }

    /**
     * Ajoute l'utilisateur en SESSION, et un jeton pour permettre de vérifier que l'utilisateur passé en SESSION est
     * bien le bon.
     *
     * @param User|null $user
     * @return void
     */
    private function connectUser(User $user): void
    {
        $this->session->set('user', $user);

        $token = hash('sha512', strlen($user->getPseudo()) . $user->getLastName());
        $this->session->set('time', $token);
    }

    /**
     * Essaye de récupérer un utilisateur dans la BDD à partir du pseudo reçu, si ça marche renvoie une erreur pour
     * indiquer que le pseudo doit être unique.
     * Essaye de récupérer un utilisateur à partir de l'adresse mail, si existe déjà renvoie une erreur.
     * Traite le mot de passe avant de l'attacher à l'utilisateur.
     * Règle la validation du mail à false. Récupère le status "Utilisateur" et l'ajoute comme valeur par défaut.
     * Persiste l'utilsateur et flush le tout en BDD.
     * Retourne true si aucune erreur ne s'est produite.
     *
     * @param User $user
     * @return bool
     * @throws UserException
     */
    private function registryUser(User &$user): bool
    {
        $userVerif = null;

        $userVerif = $this->doctrine->getRepository(User::class)
            ->findOneBy(['pseudo' => $user->getPseudo()]);
        if (!is_null($userVerif)) {
            throw new UserException('Le pseudo choisi existe déjà.', UserException::PSEUDO_EXIST);
        }

        $userVerif = $this->doctrine->getRepository(User::class)
            ->findOneBy(['mail' => $user->getMail()]);
        if (!is_null($userVerif)) {
            throw new UserException('L\'adresse mail choisie existe déjà.', UserException::MAIL_EXIST);
        }

        $password = hash('sha512', strlen($user->getPassword()) . $user->getPassword());

        /** @var Status $status */
        $status = $this->doctrine->getRepository(Status::class)
            ->findOneBy(['name' => 'utilisateur']);

        $user->setPassword($password)
            ->setMailValidate(0)
            ->setStatus($status);

        $userManager = $this->doctrine->getManager();
        $userManager->persist($user);
        $userManager->flush();
        return true;
    }
}

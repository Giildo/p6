<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use App\Exception\UserException;
use App\Form\UserType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * Gère la connexion, l'enregistrement d'un nouvel utilisateur et la déconnexion.
 *
 * @Route(name="user_")
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

    public function __construct(Environment $twig, RegistryInterface $doctrine, SessionInterface $session)
    {
        parent::__construct($twig);

        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    /**
     * Vérifie si on vient de la page "enregistrement" qui laisse dans la Session la version de l'utilisateur qui vient
     * de créer un compte. Si ce n'est pas la cas, crée un nouvel utilisateur.
     * Récupère le formulaire de connexion @uses UserType et lui associe l'utilisateur ci-dessus.
     * Attache le formulaire à la requête pour vérifier s'il a été validé et envoyé en POST. Puis valide s'il est valide.
     * Si c'est le cas,
     * @uses UserController::userVerif(). S'il y a une erreur ajoute l'erreur récupérée dans le formulaire. Puis,
     * @uses UserController::connectUser(), pour connecter l'utilisateur et le renvoyer vers l'accueil.
     *
     * @Route("/connexion", name="connection")
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function connection(Request $request)
    {
        if (!$this->isConnected()) {
            if ($this->session->has('userTransfert')) {
                $user = $this->session->get('userTransfert');
                $this->session->remove('userTransfert');
            } else {
                $user = new User();
            }

            $form = $this->createForm(UserType::class, $user);

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
                    return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
                }
            }

            return $this->render('user/connection.html.twig', [
                'form'      => $form->createView(),
                'pageTitle' => 'Se connecter',
                'pageType'  => 'connection'
            ]);
        } else {
            return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
        }
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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function registry(Request $request)
    {
        if (!$this->isConnected()) {
            $user = new User();

            $form = $this->createForm(UserType::class, $user);

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
                    return new RedirectResponse('/connexion');
                }
            }

            return $this->render('user/connection.html.twig', [
                'form'      => $form->createView(),
                'pageTitle' => "S'enregistrer",
                'pageType'  => 'registry'
            ]);
        } else {
            return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
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

        return new RedirectResponse('/accueil', RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Vérifie que l'utilisateur qui essaye d'accéder à la page est administrateur.
     * Si c'est le cas, récupère tous les utilisateurs et les envoie à la vue.
     *
     * @Route("/admin/utilisateurs", name="admin_users")
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function adminUsers()
    {
        if ($this->isAdmin()) {
            $users = $this->doctrine->getRepository(User::class)
                ->findAll();

            return $this->render('/admin/users.html.twig', compact('users'));
        } else {
            return new RedirectResponse('/erreur/401');
        }
    }

    /**
     * Vérifie que l'utilisateur qui essaye d'accéder à la page est administrateur.
     * Récupère l'utilisateur à modifier. Crée un formulaire @uses UserType.
     * Attache le formulaire à la requête, vérifie s'il a été soumis et s'il est valide.
     * Si c'est le cas, récupère le manager pour sauvegarder l'utilisateur modifié en BDD.
     *
     * @Route("/admin/modifierUtilisateur/{pseudo}", name="admin_modify", requirements={"pseudo"="\w+"})
     * @param Request $request
     * @param string $pseudo
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function modify(Request $request, string $pseudo)
    {
        if ($this->isAdmin()) {
            /** @var User $user */
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['pseudo' => $pseudo]);

            $form = $this->createForm(UserType::class, $user);
            $form->remove('password')
                ->add('password', HiddenType::class)
                ->remove('mailValidate')
                ->add('mailValidate', HiddenType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager = $this->doctrine->getManager();
                $manager->flush();
            }

            return $this->render('/admin/modifyUser.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        } else {
            return new RedirectResponse('/erreur/401');
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

        $token = hash('sha512', $user->getId() . strlen($user->getPseudo()) . $user->getLastName());
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

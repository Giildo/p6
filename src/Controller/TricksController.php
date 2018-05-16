<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Twig\Environment;

/**
 * @Route("/p6/trick", name="tricks_")
 * Class TricksController
 * @package App\Controller
 */
class TricksController extends AppController
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(Environment $twig, RegistryInterface $doctrine, SessionInterface $session)
    {
        parent::__construct($twig);
        $this->doctrine = $doctrine;
    }

    /**
     * Page d'administration des figures pour les administrer plus simplement.
     * Récupère les figures :
     * - Toutes si l'adresse est simple
     * - Filtré selon la catégorie ou l'auteur si filtré
     * S'il n'y a pas de figures trouvées selon les critères de filtre, retourne à la page sans filtre.
     * Crée un tableau de jetons pour la sécurité pour la modification et la suppression.
     *
     *
     * @Route("/liste/{category}/{options}",
     *     name="index",
     *     defaults={"category"=null, "options"=null},
     *     requirements={"category"="auteur|categorie", "options"="\w+"})
     * @param null|string $category
     * @param null|string $options
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(?string $category = null, ?string $options = null)
    {
        if ($this->isContrib()) {
            $filtered = false;
            $tricks = null;

            if (is_null($options) && is_null($category)) {
                $tricks = $this->doctrine->getRepository(Trick::class)
                    ->findAll();

                if (empty($tricks)) {
                    return $this->redirectToHome();
                }
            } else {
                /** @var TrickRepository $trickRepository */
                $trickRepository = $this->doctrine->getRepository(Trick::class);
                $filtered = true;

                if ($category === 'auteur') {
                    $tricks = $trickRepository->findByAuthor($options);
                    $category = ucfirst($category);
                } elseif ($category === 'categorie') {
                    $tricks = $trickRepository->findByCategory($options);
                    $category = 'Catégorie';
                }
            }

            if (empty($tricks)) {
                return $this->redirectToRoute('tricks_index');
            }

            /** @var Trick $trick */
            $tokens = [];
            $date = new DateTime();
            foreach ($tricks as $trick) {
                $tokens[$trick->getId()] = hash(
                    'sha512',
                    $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
                );
            }

            return $this->render('/tricks/index.html.twig', compact(
                'tricks',
                'filtered',
                'category',
                'options',
                'tokens'
            ));
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * Permet de modifier une figure.
     * Récupère la figure à modifier, si elle n'existe pas retourne vers la liste des figures.
     * Crée un jeton de vérification et me compare à celui reçu en POST.
     * Si tout est OK, crée un formulaire de type @uses TrickType. Il attache le formulaire à la requête reçue par la
     * méthode, et si tout est OK modifie la figure en BDD et renvoie l'utilisateur vers la liste des figures.
     *
     * @Route("/modifier/{id}", name="modify", requirements={"id"="\d+"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function modify(Request $request, int $id)
    {
        if ($this->isContrib()) {
            //Récupère la Trick
            /** @var Trick $trick */
            $trick = $this->doctrine->getRepository(Trick::class)
                ->find($id);

            //Si elle est nulle renvoie vers la page d'accueil
            if (is_null($trick)) {
                return $this->redirectToRoute('tricks_index');
            }

            //Création des variables nécessaires
            $date = new DateTime();
            $tokens = [];
            $manager = $this->doctrine->getManager();

            if (!is_null($trick->getHeadPicture())) {
                $tokens['header'] = hash(
                    'sha512',
                    $trick->getHeadPicture()->getName() . $date->format('m') . $trick->getHeadPicture()->getExt()
                );
            }

            //Supprime les vidéos et les images associées à la figure pour éviter qu'ils ne s'affichent dans le formulaire
            /** @var Picture $picture */
            $pictures = $trick->getPictures()->toArray();
            if (!empty($pictures)) {
                foreach ($pictures as $picture) {
                    $trick->removePicture($picture);

                    $tokens['pictures'][$picture->getName()] = hash(
                        'sha512',
                        $picture->getName() . $date->format('m') . $picture->getExt()
                    );
                }
            }

            /** @var Video $video */
            $videos = $trick->getVideos()->toArray();
            if (!empty($videos)) {
                foreach ($videos as $video) {
                    $trick->removeVideo($video);

                    $tokens['videos'][$video->getName()] = hash(
                        'sha512',
                        $video->getName() . $date->format('m')
                    );
                }
            }

            //Vérifie les tokens d'arrivée sur la page
            if (is_null($request->request->get('media'))) {
                $tokenVerif = hash(
                    'sha512',
                    $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
                );

                if ($tokenVerif !== $request->request->get('token') && empty($request->request->get('trick'))) {
                    return $this->redirectToRoute('tricks_index');
                }
                // Si ce n'est pas le cas vérifie si on arrive par le biais des boutons de suppresion des médias
            } else {
                $delete = $request->request->get('media')['delete'];
                $response = null;

                //Cas de la supression de l'image à la Une
                if (isset($delete['header']) && $tokens['header'] === $delete['header']) {
                    /** @var Picture $headPicture */
                    $headPicture = $trick->getHeadPicture();

                    if (unlink($headPicture->getUploadRootDir('tricks') . '/'
                        . $headPicture->getName() . '.'
                        . $headPicture->getExt())) {

                        $this->addVideos($trick, $manager, $videos);
                        $this->addPictures($trick, $manager, $pictures);

                        $trick->setHeadPicture(null);
                        $manager->remove($headPicture);
                        $manager->persist($trick);
                        $manager->flush();

                        return $this->redirectToRoute('tricks_modify', ['id' => $id]);
                    }
                } elseif (
                    isset($delete['picture']) &&
                    $delete['picture'][key($delete['picture'])] === $tokens['pictures'][key($delete['picture'])]
                ) {
                    /** @var Picture $picture */
                    foreach ($pictures as $key => $picture) {
                        if ($picture->getName() === key($delete['picture'])) {
                            $deletePicture = $picture;

                            if (unlink($deletePicture->getUploadRootDir('tricks') . '/'
                                . $deletePicture->getName() . '.'
                                . $deletePicture->getExt())) {

                                array_splice($pictures, $key, 1);

                                $this->addPictures($trick, $manager, $pictures);
                                $this->addVideos($trick, $manager, $videos);

                                $manager->remove($deletePicture);
                                $manager->persist($trick);
                                $manager->flush();

                                return $this->redirectToRoute('tricks_modify', ['id' => $id]);
                            }
                        }
                    }
                } elseif (
                    isset($delete['video']) &&
                    $delete['video'][key($delete['video'])] === $tokens['videos'][key($delete['video'])]
                ) {
                    /** @var Picture $picture */
                    foreach ($videos as $key => $video) {
                        if ($video->getName() === key($delete['video'])) {
                            $deleteVideo = $video;

                            array_splice($videos, $key, 1);

                            $this->addVideos($trick, $manager, $videos);
                            $this->addPictures($trick, $manager, $pictures);

                            $trick->removeVideo($deleteVideo);
                            $manager->remove($deleteVideo);
                            $manager->persist($trick);
                            $manager->flush();

                            return $this->redirectToRoute('tricks_modify', ['id' => $id]);
                        }
                    }
                }
            }

            $form = $this->createForm(TrickType::class, $trick);
            $form->remove('createdAt')
                ->remove('updatedAt')
                ->remove('user');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $trick->setUpdatedAt(new DateTime());

                $this->setHeadPicture($trick, $manager);
                $this->addPictures($trick, $manager, $pictures);
                $this->addVideos($trick, $manager, $videos);

                $manager->persist($trick);
                $manager->flush();
                return $this->redirectToRoute('tricks_index');
            }

            return $this->render('/tricks/modify.html.twig', [
                'form'     => $form->createView(),
                'trick'    => $trick,
                'pictures' => $pictures,
                'videos'   => $videos,
                'tokens'   => $tokens
            ]);
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * @Route("/supprimer/{id}", name="delete", requirements={"id"="\w+"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(Request $request, int $id): RedirectResponse
    {
        if ($this->isContrib()) {
            /** @var Trick $trick */
            $trick = $this->doctrine->getRepository(Trick::class)
                ->find($id);

            if (is_null($trick)) {
                return $this->redirectToRoute('tricks_index');
            }

            $date = new DateTime();
            $tokenVerif = hash(
                'sha512',
                $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
            );

            if (!is_null($trick->getHeadPicture())) {
                /** @var Picture $picture */
                $picture = $trick->getHeadPicture();
                unlink("{$picture->getUploadRootDir('tricks')}/{$picture->getName()}.{$picture->getExt()}");
            }

            if (!empty($pictures = $trick->getPictures()->toArray())) {
                /** @var Picture $picture */
                foreach ($pictures as $picture) {
                    unlink("{$picture->getUploadRootDir('tricks')}/{$picture->getName()}.{$picture->getExt()}");
                }
            }


            if ($tokenVerif === $request->request->get('token')) {
                $manager = $this->doctrine->getManager();
                $manager->remove($trick);
                $manager->flush();
            }

            return $this->redirectToRoute('tricks_index');
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * @Route("/{category}/{slug}/{id}/{action}",
     *     name="show",
     *     defaults={"action"=null, "id"=null},
     *     requirements={"category"="\w+", "slug"="\w+", "id"="\d+", "action"="del"})
     * @param Request $request
     * @param SessionInterface $session
     * @param string $slug
     * @param string $category
     * @param null|string $action
     * @param int $id
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function show(Request $request, SessionInterface $session, string $slug, string $category, ?string $action = null, ?int $id = null)
    {
        /** @var Trick $trick */
        $trick = $this->doctrine->getRepository(Trick::class)
            ->findOneBy(["slug" => $slug]);

        if (!is_null($trick) && $trick->getCategory()->getName() === $category) {
            $date = new DateTime();

            if (!is_null($id)) {
                $comment = $this->doctrine->getRepository(Comment::class)
                    ->find($id);

                $tokenVerif = hash(
                    'sha512',
                    $comment->getId() . $date->format('d') . $comment->getComment()
                );

                $token = $request->request->get('token');
                $requestPost = $request->request->get('comment');

                if (is_null($comment) || ($tokenVerif !== $token && !isset($requestPost))) {
                    return $this->redirectToRoute('tricks_show', [
                        'category' => $trick->getCategory()->getName(),
                        'slug'     => $trick->getSlug()
                    ]);
                }

                if ($action === 'del') {
                    $manager = $this->doctrine->getManager();
                    $manager->remove($comment);
                    $manager->flush();

                    return $this->redirectToRoute('tricks_show', [
                        'category' => $trick->getCategory()->getName(),
                        'slug'     => $trick->getSlug()
                    ]);
                }
            } else {
                $comment = new Comment();
            }

            $form = $this->createForm(CommentType::class, $comment);
            $form->remove('createdAt')
                ->remove('updatedAt')
                ->remove('trick')
                ->remove('user');

            /** @var User $userIndentify */
            $userIndentify = $session->get('user');
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var User $user */
                $user = $this->doctrine->getRepository(User::class)
                    ->find($userIndentify->getId());

                if (is_null($id)) {
                    $comment->setCreatedAt($date)
                        ->setUpdatedAt($date)
                        ->setUser($user)
                        ->setTrick($trick);
                } else {
                    $comment->setUpdatedAt($date);
                }

                $manager = $this->doctrine->getManager();
                $manager->persist($comment);
                $manager->flush();

                return $this->redirectToRoute('tricks_show', [
                    'category' => $trick->getCategory()->getName(),
                    'slug'     => $trick->getSlug()
                ]);
            }

            $comments = $this->doctrine->getRepository(Comment::class)
                ->findBy(['trick' => $trick], ['updatedAt' => 'desc']);

            /** @var Comment $comment */
            $tokens = [];
            if (!is_null($userIndentify)) {
                foreach ($comments as $comment) {
                    $tokens[$comment->getId()] = ($comment->getUser()->getId() === $userIndentify->getId()) ?
                        hash('sha512',
                            $comment->getId() . $date->format('d') . $comment->getComment()
                        ) : null;
                }
            }

            return $this->render('tricks/show.html.twig', [
                'trick'    => $trick,
                'comments' => $comments,
                'form'     => $form->createView(),
                'tokens'   => $tokens
            ]);
        } else {
            return $this->redirectToHome();
        }
    }

    /**
     * @Route("/ajouter", name="add")
     * @param Request $request
     * @param SessionInterface $session
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function add(Request $request, SessionInterface $session)
    {
        if ($this->isConnected()) {
            $trick = new Trick();

            $form = $this->createForm(TrickType::class, $trick);
            $form->remove('createdAt')
                ->remove('updatedAt')
                ->remove('user');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var User $userConnected */
                $userConnected = $this->doctrine->getRepository(User::class)
                    ->find($session->get('user')->getId());

                $dateTime = new DateTime();

                $trick->setCreatedAt($dateTime)
                    ->setUpdatedAt($dateTime)
                    ->setUser($userConnected);

                $manager = $this->doctrine->getManager();

                //Ajout des images dans les tricks
                $this->addPictures($trick, $manager);
                $this->addVideos($trick, $manager);

                $this->setHeadPicture($trick, $manager);
                $manager->persist($trick);
                $manager->flush();

                return $this->redirectToHome();
            }

            return $this->render('/tricks/add.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return $this->redirectToError(401);
        }
    }

    /**
     * @param Trick $trick
     * @param ObjectManager $manager
     */
    private function setHeadPicture(Trick &$trick, ObjectManager $manager): void
    {
        $headPicture = $trick->getHeadPicture();

        if (!is_null($headPicture)) {
            $headPicture->setAlt("Image à la une de la figure {$trick->getName()}")
                ->setName("head_{$trick->getSlug()}")
                ->upload('tricks');

            $manager->persist($headPicture);
        }
    }

    /**
     * @param Trick $trick
     * @param ObjectManager $manager
     * @param array|null $pictures
     * @return void
     */
    private function addPictures(Trick &$trick, ObjectManager $manager, ?array $pictures = []): void
    {
        $i = 1;

        $date = new DateTime();

        /** @var Picture $picture */
        foreach ($trick->getPictures()->toArray() as $picture) {
            $picture->setAlt("Image associée à la figure {$trick->getName()}")
                ->setName($trick->getSlug() . $date->format('YmdHis') . $i)
                ->upload('tricks');

            $manager->persist($picture);

            $i++;
        }

        if (!empty($pictures)) {
            foreach ($pictures as $picture) {
                $trick->addPicture($picture);
            }
        }
    }

    /**
     * @param Trick $trick
     * @param ObjectManager $manager
     * @param array|null $videos
     * @return void
     */
    private function addVideos(Trick &$trick, ObjectManager $manager, ?array $videos = []): void
    {
        /** @var Video $video */
        foreach ($trick->getVideos()->toArray() as $video) {
            $videoName = explode('v=', $video->getName());

            $video->setName($videoName[1]);

            $manager->persist($video);
        }

        if (!empty($videos)) {
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }
        }
    }

    /**
     * @param Trick $trick
     * @param Picture $deletePicture
     * @param ObjectManager $manager
     * @param array|null $videos
     * @param array|null $pictures
     * @return RedirectResponse|null
     */
    private function removePicture(Trick &$trick, Picture $deletePicture, ObjectManager $manager, int $id, ?array $videos = [], ?array $pictures = []): ?RedirectResponse
    {
        if (unlink($deletePicture->getUploadRootDir('tricks') . '/'
            . $deletePicture->getName() . '.'
            . $deletePicture->getExt())) {
            $this->addVideos($trick, $manager, $videos);
            $this->addPictures($trick, $manager, $pictures);

            $trick->setHeadPicture(null);
            $manager->remove($deletePicture);
            $manager->persist($trick);
            $manager->flush();

            return $this->redirectToRoute('tricks_modify', ['id' => $id]);
        } else {
            return null;
        }
    }

    private function removeHeadPicture(Trick &$trick, Picture $deletePicture, ObjectManager $manager, int $id, ?array $videos = [], ?array $pictures = []): ?RedirectResponse
    {
        $response = $this->removePicture($trick, $deletePicture, $manager, $id, $videos, $pictures);

        if (!is_null($response)) {
            $trick->setHeadPicture(null);
            $manager->persist($trick);
            $manager->flush();
        }

        return $response;
    }
}

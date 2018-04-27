<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/trick", name="tricks_")
 * Class TricksController
 * @package App\Controller
 */
class TricksController extends AppController
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(Environment $twig, RegistryInterface $doctrine)
    {
        parent::__construct($twig);
        $this->doctrine = $doctrine;
    }

    /**
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

            if (is_null($options) && is_null($category)) {
                $tricks = $this->doctrine->getRepository(Trick::class)
                    ->findAll();
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
            return new RedirectResponse('/accueil');
        }
    }

    /**
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
            /** @var Trick $trick */
            $trick = $this->doctrine->getRepository(Trick::class)
                ->find($id);

            if (is_null($trick)) {
                return new RedirectResponse('/trick/liste');
            }

            $date = new DateTime();
            $tokenVerif = hash(
                'sha512',
                $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
            );

            if ($tokenVerif !== $request->request->get('token')) {
                return new RedirectResponse('/trick/liste');
            }

            $form = $this->createForm(TrickType::class, $trick);
            $form->remove('createdAt')
                ->remove('updatedAt')
                ->remove('user');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $trick->setUpdatedAt(new DateTime());

                $manager = $this->doctrine->getManager();
                $manager->flush();
                return new RedirectResponse('/trick/liste');
            }

            return $this->render('/tricks/add.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return new RedirectResponse('/accueil');
        }
    }

    /**
     * @Route("/supprimer/{id}", name="delete", requirements={"id"="\w+"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(Request $request, int $id) {
        if ($this->isContrib()) {
            $trick = $this->doctrine->getRepository(Trick::class)
                ->find($id);

            if (is_null($trick)) {
                return new RedirectResponse('/trick/liste');
            }

            $date = new DateTime();
            $tokenVerif = hash(
                'sha512',
                $trick->getId() . $date->format('d') . $trick->getName() . $date->format('m')
            );

            if ($tokenVerif !== $request->request->get('token')) {
                return new RedirectResponse('/trick/liste');
            }

            $manager = $this->doctrine->getManager();
            $manager->remove($trick);
            $manager->flush();

            return new RedirectResponse('/trick/liste');
        } else {
            return new RedirectResponse('/accueil');
        }
    }

    /**
     * @Route("/{category}/{slug}", name="show", requirements={"category"="\w+", "slug"="\w+"})
     * @param FormFactoryInterface $formBuilder
     * @param string $slug
     * @param string $category
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function show(FormFactoryInterface $formBuilder, string $slug, string $category)
    {
        $trick = $this->doctrine
            ->getRepository(Trick::class)
            ->findOneBy(["slug" => $slug]);

        if (!is_null($trick) && $trick->getCategory()->getName() === $category) {
            $form = $formBuilder->createBuilder()
                ->add('comment', TextareaType::class, ['label' => 'Laisser un commentaire'])
                ->getForm();

            $comments = $this->doctrine
                ->getRepository(Comment::class)
                ->findBy(['trick' => $trick], ['updatedAt' => 'desc']);

            return $this->render('tricks/show.html.twig', [
                'trick'    => $trick,
                'comments' => $comments,
                'form'     => $form->createView()
            ]);
        } else {
            return new RedirectResponse('/accueil', RedirectResponse::HTTP_FOUND);
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
                $manager->persist($trick);
                $manager->flush();

                return new RedirectResponse('/accueil');
            }

            return $this->render('/tricks/add.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return new RedirectResponse('/accueil');
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\TrickType;
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
            $essai = $this->doctrine->getRepository(Trick::class)
                ->find(1);

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

                $trick->setCreatedAt(new DateTime('now'))
                    ->setUpdatedAt($trick->getCreatedAt())
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

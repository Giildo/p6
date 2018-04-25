<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trick", name="tricks_")
 * Class TricksController
 * @package App\Controller
 */
class TricksController extends AppController
{
    /**
     * @Route("/show/{category}/{slug}", name="show", requirements={"category"="\w+", "slug"="\w+"})
     * @param RegistryInterface $doctrine
     * @param FormFactoryInterface $formBuilder
     * @param string $slug
     * @param string $category
     * @return Response|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function show(RegistryInterface $doctrine, FormFactoryInterface $formBuilder, string $slug, string $category)
    {
        $trick = $doctrine
            ->getRepository(Trick::class)
            ->findOneBy(["slug" => $slug]);

        if (!is_null($trick) && $trick->getCategory()->getName() === $category) {
            $form = $formBuilder->createBuilder()
                ->add('comment', TextareaType::class, ['label' => 'Laisser un commentaire'])
                ->getForm();

            $comments = $doctrine
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
}

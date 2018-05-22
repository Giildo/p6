<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\Type\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/p6/erreur", name="error_")
 * Class ErrorController
 * @package App\Controller
 */
class ErrorController extends AppController
{
    /**
     * @Route("/401", name="401")
     * @param Request $request
     * @return Response
     */
    public function e401(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(ContactType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            mail(
                'giildo.jm@gmail.com',
                $message->getSubject(),
                $message->getMessage()
            );

            return $this->redirectToRoute('error_401');
        }

        return $this->render('bundles/TwigBundle/Exception/error401.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/500", name="500")
     * @param Request $request
     * @return Response
     */
    public function e500(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(ContactType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            mail(
                'giildo.jm@gmail.com',
                $message->getSubject(),
                $message->getMessage()
            );

            return $this->redirectToRoute('error_500');
        }

        return $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

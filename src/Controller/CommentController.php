<?php

namespace App\Controller;

use App\Entity\Comment;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route(name="comment_")
 * Class CommentController
 * @package App\Controller
 */
class CommentController extends AppController
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
     * @Route("/admin/commentaires/{category}/{value}",
     *     name="admin_index",
     *     defaults={"category"=null,"value"=null},
     *     requirements={"category"="utilisateur|trick", "value"="\w+"}
     * )
     * @param RegistryInterface $doctrine
     * @param null|string $category
     * @param null|string $value
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(?string $category = null, ?string $value = null)
    {
        if ($this->isAdmin()) {
            $filtered = false;
            $comments = [];
            if (!is_null($category) && !is_null($value)) {
                if ($category === 'utilisateur') {
                    $comments = $this->doctrine->getRepository(Comment::class)
                        ->findByUser($value);
                } elseif ($category === 'trick'){
                    $comments = $this->doctrine->getRepository(Comment::class)
                        ->findByTrick($value);
                }

                $filtered = true;
            } else {
                $comments = $this->doctrine->getRepository(Comment::class)
                    ->findAll();
            }

            /** @var Comment $comment */
            $tokens = [];
            $date = new DateTime();
            foreach ($comments as $comment) {
                $tokens[$comment->getId()] = hash(
                    'sha512',
                    $date->format('m') . $comment->getUser()->getPseudo() . $date->format('d') . $comment->getId()
                );
            }

            return $this->render('admin/comments.html.twig', compact(
                'comments',
                'filtered',
                'category',
                'value',
                'tokens'
            ));
        } else {
            return new RedirectResponse('/accueil');
        }
    }

    /**
     * @Route("/admin/commentaires/supprimer/{id}", name="admin_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(Request $request, int $id)
    {
        if ($this->isAdmin()) {
            $comment = $this->doctrine->getRepository(Comment::class)
                ->find($id);

            if (!is_null($comment)) {
                $token = $request->request->get('token');

                $date = new DateTime();
                $tokenVerif = hash(
                    'sha512',
                    $date->format('m') . $comment->getUser()->getPseudo() . $date->format('d') . $comment->getId()
                );

                if ($token === $tokenVerif) {
                    $manager = $this->doctrine->getManager();
                    $manager->remove($comment);
                    $manager->flush();

                    return new RedirectResponse('/admin/commentaires');
                }
            }

            return new RedirectResponse('/admin/commentaires');

        } else {
            return new RedirectResponse('/admin/commentaires');
        }
    }
}

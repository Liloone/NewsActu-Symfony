<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\CommentaryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentaryController extends AbstractController
{
    /**
     * @Route("/ajouter-un-commentaire?article_id={id}", name="add_commentary", methods="GET|POST")
     */
    public function addCommentary(Article $article,Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentary = new Commentary();
        $form = $this->createForm(CommentaryFormType::class, $commentary)->handleRequest($request);
        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }
}


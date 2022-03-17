<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    /**
     * @Route("/voir/{cat_alias}/{article_alias}_{id}.html", name="show_article", methods={"GET"})
     */
    public function showAricle(Article $article, EntityManagerInterface $entityManager): Response
    {
        return $this->render('article/show_article.html.twig', [
            'article' => $article,
        ]);
    }
}

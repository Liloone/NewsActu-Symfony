<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\CommentaryFormType;
use DateTime;
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
    public function addCommentary(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentary = new Commentary();
        $form = $this->createForm(CommentaryFormType::class, $commentary)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() === false) {

            $this->addFlash('warning', 'votre commentaire est vide !');

            return $this->redirectToRoute('show_article', [
                'cat_alias' => $article->getCategory()->getAlias(),
                'article_alias' =>  $article->getAlias(),
                'id' => $article->getId()
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $commentary->setArticle($article);
            $commentary->setCreatedAt(new DateTime());
            $commentary->setUpdatedAt(new DateTime());

            $commentary->setAuthor($this->getUser());

            $entityManager->persist($commentary);
            $entityManager->flush();

            $this->addFlash('success', "Vous avez commenté l'article <stong>" . $article->getTitle() . "</strong> avec succès ! ");

            return $this->redirectToRoute('show_article', [
                'cat_alias' => $article->getCategory()->getAlias(),
                'article_alias' =>  $article->getAlias(),
                'id' => $article->getId()
            ]);
        }


        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * 1ère FAÇON :
     *              Inconvenients : - C'est très verbeux.
     *                              - Les paramètres attendu de la route pour faire un redirectToRoute() peuvent ne pas être accessibles.
     *              Avantage      : - La rediretion sera statique tous les utilisateurs seront redirigés au même endroit.  
     * 
     * 2ème FAÇON : 
     *              Inconvenient  : - La redirection se fera en fonction de l'url de provenance de la requête, à savoir si vous utilisez cette action à plusieurs endroits différents de votre site, l'utilisateur sera redirigé ailleurs que ce que vous avez décidé.
     *              Avantage      : - La redirection devient dynamique. (elle changra en fonction de la provenance de la requête)

     * 
     * @Route("/archiver-mon-commentaire_{id}", name="soft_delete_commentary", methods={"GET"})
     */
    public function softDeleteCommentary(Commentary $commentary, EntityManagerInterface $entityManager, Request $request): Response
    {
        /**
         *  Parce que nous allons rediriger vers show_article qui attend 3 aguments, nous avons injecté Request ↑↑↑
         *  Cela nous permettra d'accéder aux superglobales PHP ($_GET & $_SERVER => appelés dans l'ordre : query & server dans l'objet Request)
         * Nous allons voir 2 façons pour rediriger sur la oute souhaitée.
         */

        $commentary->setDeletedAt(new DateTime());
        #================= 1ère FAÇON =========================#
        //        dd($request->query->get('article_alias'));
        #================= 2ème FAÇON =========================#
        //     dd($request->server->get('HTTP_REFERER'));

        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success', "Votre commentaire à bien été archivé");

        #================= 1ère FAÇON =========================#

        # La construction de l'URL à lieu dans le fichier 'show_article.html.twig' sur l'attribut HTML 'href' de la balise <a>
        # ===> Voir pour la suite de la 1ère FAÇON

        # Ici nous réccupérons les valeurs des paramètres passés dans l'url $_GET (query)
        //    return $this->redirectToRoute('show_article', [
        //    'cat_alias' => $request->query->get('cat_alias'),
        //    'article_alias' => $request->query->get('article_alias'),
        //    'id' => $request->query->get('article_id')
        //        ]);
        #=========================================================#

        #================= 2ème FAÇON =========================#
            # POur cette façon nous avons retiré les paramètres dans le fichier 'show_article.html.twig'

            # Ici nous utilisons une clé du $_SERVER (server) qui s'appelle 'HTTP_REFERER'.
            # Cette clé contient l'URL de provenance de la requête ($request) 

        return $this->redirect($request->server->get('HTTP_REFERER'));
        #=========================================================#
        // return $this->redirectToRoute('show_article');
    }

     /**
     * @Route("/restaurer-un-commentaire/{id}", name="restore_commentary", methods={"GET"})
     */
    public function restoreCommentary(Commentary $commentary, EntityManagerInterface $entityManager, Request $request): Response
    {
        $commentary->setDeletedAt();

        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success', "Votre commentaire à bien été restaurée");

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

}

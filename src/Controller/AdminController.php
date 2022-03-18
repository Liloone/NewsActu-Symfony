<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/tableau-de-board", name="show_dashboard", methods={"GET"})
     */
    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll(['deletedAt' => null]);
       

        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
        ]);

    }

    /**
     * @Route("/crer-un-article",name="create_article", methods={"GET|POST"})
     */
    public function createArticle(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour accéder à une valeur d'un input de $form, on fait :
            // $form->get('title')->getData()

            // Setting des propriétés non mappés dans le formulaire
            $article->setAlias($slugger->slug($article->getTitle()));
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());

            //Variabilisation du fichier
            $file = $form->get('photo')->getData();

            // if (isset($file) === true)
            // si un fichier est uploadé (depuis le formulaire)
            if ($file) {
                // Maintenant il s'agit de reconstruire le nom du fichier pour le sécuriser

                // 1ère Étape : on déconstruit le nom du fichier et on le variabilise.

                $extension = '.' . $file->guessExtension();
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);


                // Assainissement du nom du fichier (du filename)
                // $safeFilename =  $slugger->slug($originalFileName);
                $safeFilename = $article->getAlias();

                // 2ème Étape : on reconstruit le nom du fichier maintnant qu'il est safe.
                // uniqd() est une fonction native de PHP, elle permet d'ajouter une valeur numérique (id) unique et auto-généré
                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                // try/catch fait partie de PHP nativement 
                try {
                    // On a configuré un paramettre dns le fichier service.yaml
                    // Ce param contient le chemin absolu de notre dossier d'upload de photo.
                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    // On set le nom de la photo par le chemin
                    $article->setPhoto($newFilename);
                } catch (FileException $exception) {
                } // END catch

            } // END if($file)

            $entityManager->persist($article);
            $entityManager->flush();

            //Ici on ajoute un message qu'on affichera en twig
            $this->addFlash('success', 'Bravo, votre article est bien en ligne !');

            return $this->redirectToRoute('show_dashboard');
        } // END if($form)

        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/modifier-un-article/{id}", name="update_article", methods={"GET|POST"})
     */
    //L'action est exécutée 2x et accessible par les deux méthods (GET|POST)
    public function updateArticle(Article $article, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        # Condition ternaire: $article->getPhoto() ?? ''
        # => est égal à : isset($article->getPhoto()) ? $article->getPhoto() : '' ;
        $originalPhoto = $article->getPhoto() ?? '';
        // 1er tour en m"thode GET
        $form = $this->createForm(ArticleFormType::class, $article, [
            'photo' => $originalPhoto
        ])->handleRequest($request);

        // 2éme TOUR de l'action en méthode POST
        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAlias($slugger->slug($article->getTitle()));
            $article->setUpdatedAt(new DateTime());

            $file = $form->get('photo')->getData();

            if ($file) {
                $extension = '.' . $file->guessExtension();
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $article->getAlias();

                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                try {

                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $article->setPhoto($newFilename);
                } catch (FileException $exception) {
                    # code à exécuteer si une erreur est attrapée
                } // END catch

            } else {
                $article->setPhoto($originalPhoto);
            } // END if($file)

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', "L'article " . $article->getTitle() . " à bien été modifié !");

            return $this->redirectToRoute("show_dashboard");
        } // END ($form)

        // On retourne la vue pour la méthode GET
        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    } // END function


  /**
     * @Route("/archiver-un-article/{id}", name="soft_delete_article", methods={"GET"})
     */
    public function softdeleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        # On set la propriété deletedAt pour archiver l'article.
            # De l'autre coté on affichera les articles où deletedAt === null

    $article->setDeletedAt(new DateTime());

    $entityManager->persist($article);
    $entityManager->flush();

    $this->addFlash('success', "L'article" . $article->getTitle() . " à bien été archivé");

    return $this->redirectToRoute('show_dashboard');
    }

     /**
     * @Route("/supprimer-un-article/{id}", name="hard_delete_article", methods={"GET"})
     */
    public function hardDeleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        # Cette méthode supprime une ligne en BDD
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', "L'article " . $article->getTitle() . " à bien été supprimé de la base de donnée");

        return $this->redirectToRoute('show_dashboard');
    }

    /**
     * @Route("/restaurer-un-article/{id}", name="restore_article", methods={"GET"})
     */
    public function restoreArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        $article->setDeletedAt();

        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success', "L'article " . $article->getTitle() . " à bien été restauré");

        return $this->redirectToRoute('show_dashboard');
    }

}// END class

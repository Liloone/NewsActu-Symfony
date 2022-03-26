<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/user")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //si l'utilisateur est déjà connecté
        if ($this->getUser()) {

            $this->addFlash('warning', 'Vous êtes déjà connecté');
            return $this->redirectToRoute('default_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/profile/changer-mon-mot-de-passe", name="change_user_password", methods={"GET|POST"})
     */
    public function changeUserPassword(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class)->handleRequest($request);

if($form->isSubmitted() && $form->isValid()) {

    $user = $this->getUser();

    $user->setPassword($passwordHasher->hashPassword($user,$form->get('plainPassword')->getData()));
    $user->setUpdatedAt(new DateTime());

    $entityManager->persist($user);
    $entityManager->flush();

    $this->addFlash('success', 'votre mot de passe à bien été modifié');
    return $this->redirectToRoute('show_profile');
        
}

        return $this->render('security/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

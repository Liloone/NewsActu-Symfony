<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegisterFormType extends AbstractType
{
# Nous avons déclaré une propiété de class, car la fonction buildForm() ne peut prendre aucune injection de dépendance.

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre email'
            ]);
        # Si c'est u update-user, alors on ne rend pas l'input de password
        # Ce champ est donc réservé à l'inscription

        if (!$this->security->getUser()) {
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Choisissez un mot de passe'
                ]);
        }

        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Votre prénom'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Votre nom'
            ])


            ->add('submit', SubmitType::class, [
                'label' => null === $this->security->getUser() ? "Je m'inscris" : "actualiser",
                'validate' => false,
                'attr' => [
                    'class' => 'd-block col-2 my-3 mx-auto btn btn-warning'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

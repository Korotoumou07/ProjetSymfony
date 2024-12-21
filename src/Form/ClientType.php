<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Client;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
       
        ->add('surname',TextType::class,[

           'required'=>false
          
        ])
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            'required' => false,
            'mapped' => false,
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom de l\'utilisateur est obligatoire']),
            ],
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'required' => false,
            'mapped' => false,
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le prénom'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le prénom de l\'utilisateur est obligatoire']),
            ],
        ])
        ->add('telephone',TextType::class,[

           'required'=>false
           
        ])
        ->add('adresse',TextType::class,[

            
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l adresse'],

            'required'=>false
            

        ])
        ->add('createAccount', CheckboxType::class, [
            'label' => 'Créer un compte :',
            'required' => false,
            'mapped' => false, // Ce champ n'est pas lié à l'entité
            'attr' => ['class' => 'form-check-input', 'onchange' => 'toggleAccountFields(this)'],
        ])
        ->add('login', TextType::class, [
            'label' => 'Login',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le login'],
            'required' => false,
            'mapped' => false,
           
        ])
        ->add('password', PasswordType::class, [
            'label' => 'Password',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le mot de passe'],
            'required' => false,
            'mapped' => false,
           
        ])
       
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientId', HiddenType::class, [
                'required' => false,
            ])

            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter last name'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
            ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter first name'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
            ],
            ])
            ->add('login', TextType::class, [
                'label' => 'Login',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le login',
                ],
                'required' => false,
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le login est obligatoire']),
            ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le mot de passe',
                ],
                'required' => false,
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le password est obligatoire']),
            ],
                
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, 
        ]);
    }
}

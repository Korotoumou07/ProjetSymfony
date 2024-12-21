<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];
        $builder
                ->add('id', HiddenType::class, [
                    'required' => false,
                ])
            
            ->add('login', TextType::class, [
                'label' => 'Login',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter login'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le login est obligatoire']),
            ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Boutiquier' => 'ROLE_BOUTIQIER',
                ],
                'required'=>false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Roles',
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le role est obligatoire']),
            ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter password'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le password est obligatoire']),
            ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Last Name',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter last name'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
            ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'First Name',
                'required'=>false,
                'attr' => ['placeholder' => 'Enter first name'],
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
            ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}

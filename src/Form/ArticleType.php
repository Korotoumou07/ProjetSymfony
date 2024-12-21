<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nomArticle', TextType::class, [
            'label' => 'Nom de l\'Article',
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom de l\'article est obligatoire.']),
               
            ],
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom de l\'article'],
        ])
        ->add('qteStock', NumberType::class, [
            'label' => 'Quantité en Stock',
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'La quantité en stock est obligatoire.']),
                
            ],
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la quantité en stock'],
        ])
        ->add('prix', NumberType::class, [
            'label' => 'Prix',
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
               
            ],
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le prix de l\'article'],
        ])
       
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

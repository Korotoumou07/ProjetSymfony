<?php

namespace App\Form;

use App\Dto\ArticleFormSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ArticleFormSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomArticle',TextType::class,[

                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'libelle'
                ]
            ])   
           
             ->add('statut', ChoiceType::class, [
                'choices' => [
                    'RUP' => 'RUP',
                    'DIS' => 'DIS',
                    'ALL' => 'ALL',
                ],
                'expanded' => true, 
                'multiple' => false,
                'label' => false,
                'attr' => [
                    'class' => 'filter-buttons',
                ],
            ]);
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleFormSearch::class,
        ]);
    }
}

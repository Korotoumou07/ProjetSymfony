<?php

namespace App\Form;

use App\Dto\ClientFormSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClientFormSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('surname',TextType::class,[

            'required'=>false,
            'label'=>false,
            'attr'=>[
                'placeholder'=>'entrer un surname'
            ]
           
         ])
         ->add('telephone',TextType::class,[

            'required'=>false,
            'label'=>false,
            'attr'=>[
                'placeholder'=>'entrer un telephone'
            ]
         ])

         ->add('statut',ChoiceType::class,[
            'label'=>false,
            'choices'  => [
                'Tout' => 'Tout',
                'Oui' => 'Oui',
                'Non' => 'Non',
            ],
        ]);

       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientFormSearch::class,
        ]);
    }
}
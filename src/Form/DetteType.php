<?php

namespace App\Form;

use App\Entity\Dette;
use App\Entity\Client;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
            
            $builder
            
            ->add('montantVerser', NumberType::class, [
                'label' => 'Montant Versé',
                'attr' => ['class' => 'form-control'],
                'required'=>false,
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le montant verser est obligatoire']),
            ],
            ])
            
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getUser() ? $client->getUser()->getNom() . ' ' . $client->getUser()->getPrenom() : '';
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.User IS NOT NULL')
                        ->orderBy('c.surname', 'ASC');
                },
                'placeholder' => 'Sélectionnez un client',
                'required'=>false,
                'constraints' => [
                new Assert\NotBlank(['message' => 'Le Client  est obligatoire']),
            ],
            ])
           

            ->add('Enregister', SubmitType::class, [
            'attr' => ['class' => 'btn btn-pimary' ],
        ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}

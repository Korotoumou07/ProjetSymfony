<?php
namespace App\Form;

use App\Entity\Dette_Article;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ArticleQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nomArticle',
                'placeholder' => 'Sélectionnez un article',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('qte', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette_Article::class, 
        ]);
    }
}

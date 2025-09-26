<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('excerpt')
            ->add('metaTitle')
            ->add('metaDescription')

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            // ->add('tags', EntityType::class, [
            //     'class' => Tag::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'article_form',
        ]);
    }
}

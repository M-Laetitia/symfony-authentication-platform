<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Enum\ArticleType;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('excerpt')
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('introduction')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => ArticleType::DRAFT,
                    'Publié' => ArticleType::PUBLISHED,
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('content', HiddenType::class, [
                'mapped' => false, 
                'required' => false,
                'attr' => ['id' => 'article_form_content']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,      
                'expanded' => true,    
                'by_reference' => false, 
            ])
            ->add('coverFile', FileType::class, [
                'mapped' => false, 
                'required' => false,
                'label' => 'Image de couverture',
                'attr' => ['accept' => 'image/*'], 
            ])
            ->add('coverAlt', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Texte alternatif pour la couverture',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer l\'article',
                'attr' => ['class' => 'btn btn-primary']
            ]);
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

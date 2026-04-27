<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArticleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryChoices = $options['category_choices'] ?? [];
        
        $builder
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Search by title',
                'attr' => [
                    'placeholder' => 'Search by title...',
                    'class' => 'filter-select',
                ],
                'constraints' => [
                    new Assert\Length(
                        min:  3,
                        max: 150,
                        minMessage: 'Search term must be at least {{min}} characters',
                        maxMessage:  'Search term cannot be longer than {{max}} characters',
                    ),
                ],
            ])
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'label' => 'Sort by',
                'choices' => [
                    'Date (Newest)' => 'date_desc',
                    'Date (Oldest)' => 'date_asc',
                ],
                'attr' => ['class' => 'filter-select'],
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'label' => 'Status',
                'choices' => [
                    'All' => '',
                    'Published' => 'published',
                    'Draft' => 'draft',
                    'Archived' => 'archived',
                ],
                'attr' => ['class' => 'filter-select'],
            ])
            ->add('featured', ChoiceType::class, [
                'required' => false,
                'label' => 'Featured',
                'choices' => [
                    'All' => '',
                    'Featured Only' => 'yes',
                    'Not Featured' => 'no',
                ],
                'attr' => ['class' => 'filter-select'],
            ])
            ->add('category', ChoiceType::class, [
                'required' => false,
                'label' => 'Category',
                'choices' => $categoryChoices,
                'placeholder' => 'All',
                'attr' => ['class' => 'filter-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'data_class' => null,
            'category_choices' => [],
        ]);
    }

    // to avoid form fields being prefixed in HTML input names
    public function getBlockPrefix(): string
    {
        return '';
    }
}

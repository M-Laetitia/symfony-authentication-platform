<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SearchArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', SearchType::class, [
                'label' => 'Search articles',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search for an article...',
                    'minlength' => 3,
                    'maxlength' => 30,
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'Enter at least {{ limit }} characters',
                        'maxMessage' => 'Maximum {{ limit }} characters allowed',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => true,
        ]);
    }
}
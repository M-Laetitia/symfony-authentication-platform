<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Category Name*',
                'attr' => [
                    'placeholder' => 'Enter category name...',
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The category name cannot be empty']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'The category name must be at least {{min}} characters',
                        'maxMessage' => 'The category name cannot exceed {{max}} characters',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description (max 500 characters)',
                'attr' => [
                    'placeholder' => 'Enter category description (optional)...',
                    'rows' => 6,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'The description cannot exceed {{max}} characters',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}

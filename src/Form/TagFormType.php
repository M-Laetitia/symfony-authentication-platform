<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TagFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Tag Name*',
                'attr' => [
                    'placeholder' => 'Enter tag name...',
                    'maxlength' => 30,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The tag name cannot be empty']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'The tag name must be at least {{min}} characters',
                        'maxMessage' => 'The tag name cannot exceed {{max}} characters',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}

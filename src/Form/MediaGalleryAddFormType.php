<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MediaGalleryAddFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('media', FileType::class, [
                'label' => 'Photo File',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a photo to upload.',
                    ]),
                    new Image([
                        'maxWidth' => 1800,
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Accepted formats: JPEG, PNG, WebP',
                    ]),
                    new File([
                        'maxSize' => '4M',
                        'maxSizeMessage' => 'The file is too large (max: 3MB).',
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                ]
            ])
            ->add('altText', TextareaType::class, [
                'label' => 'Image Description (Alt Text) - 150 characters max',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please provide an image description for accessibility.',
                    ]),
                    new Length([
                        'max' => 150,
                        'maxMessage' => 'The alt text cannot exceed 150 characters.',
                    ]),
                ],
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Describe what\'s in the photo for accessibility...',
                    'maxlength' => 150,
                ]
            ])
            ->add('caption', TextareaType::class, [
                'label' => 'Caption (Optional)',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Add a caption or details about this photo...',
                ]
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Make this a featured image',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

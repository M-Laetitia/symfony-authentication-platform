<?php

namespace App\Form;

use App\Entity\Speciality;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class MediaGalleryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        if (!$isEdit) {
            $builder->add('media', FileType::class, [
                'label' => 'Photo File',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please select a photo to upload.']),
                    new Image([
                        'maxWidth' => 1800,
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Accepted formats: JPEG, PNG, WebP',
                    ]),
                    new File([
                        'maxSize' => '4M',
                        'maxSizeMessage' => 'The file is too large (max: 4MB).',
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'id' => 'media-file-input',
                ],
            ]);
        }

        $builder
            ->add('altText', TextareaType::class, [
                'label' => 'Image Description (Alt Text) - 150 characters max',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please provide an image description for accessibility.']),
                    new Length(['max' => 150, 'maxMessage' => 'The alt text cannot exceed 150 characters.']),
                ],
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Describe what\'s in the photo for accessibility...',
                    'maxlength' => 150,
                ],
            ])
            ->add('caption', TextareaType::class, [
                'label' => 'Caption (Optional) - 255 characters max',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'The caption cannot exceed 255 characters.']),
                ],
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Add a caption or details about this photo...',
                    'maxlength' => 255,
                ],
            ])
            ->add('speciality', EntityType::class, [
                'class' => Speciality::class,
                'choice_label' => 'name',
                'label' => 'Speciality',
                'required' => true,
                'placeholder' => 'Select a speciality...',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a speciality for this photo.']),
                ],
            ])
            ->add('featured', CheckboxType::class, [
                'label' => '★ Mark as Featured Photo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}

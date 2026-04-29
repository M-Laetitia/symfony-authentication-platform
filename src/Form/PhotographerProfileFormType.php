<?php

namespace App\Form;

use App\Entity\Photographer;
use App\Entity\Speciality;
use App\Enum\PhotographerStatusType;
use App\Enum\PhotographerVisibilityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PhotographerProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Basic Info
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 30]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 30]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Account Status',
                'mapped' => false,
                'choices' => [
                    'Active' => PhotographerStatusType::ACTIVE->value,
                    'Inactive' => PhotographerStatusType::INACTIVE->value,
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Profile Visibility',
                'mapped' => false,
                'choices' => [
                    'Public' => PhotographerVisibilityType::PUBLIC->value,
                    'Private' => PhotographerVisibilityType::PRIVATE->value,
                ],
                'expanded' => true,
                'required' => true,
            ])

            // Bio Section
            ->add('bioQuote', TextType::class, [
                'label' => 'Quote/Tagline',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 255])],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('bioShort', TextareaType::class, [
                'label' => 'Short Bio',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 1000])],
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('bioLong', TextareaType::class, [
                'label' => 'Full Bio',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 5000])],
                'attr' => ['class' => 'form-control', 'rows' => 8],
            ])

            // Info Section
            ->add('location', TextType::class, [
                'label' => 'Location',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('languages', TextType::class, [
                'label' => 'Languages (comma separated)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // Experience Section
            ->add('experienceYears', IntegerType::class, [
                'label' => 'Years of Experience',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('shootingsCount', IntegerType::class, [
                'label' => 'Number of Shootings',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('equipment', TextType::class, [
                'label' => 'Equipment (comma separated)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // Links Section
            ->add('website', TextType::class, [
                'label' => 'Website',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('behance', TextType::class, [
                'label' => 'Behance',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('tiktok', TextType::class, [
                'label' => 'TikTok',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('youtube', TextType::class, [
                'label' => 'YouTube',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('twitter', TextType::class, [
                'label' => 'Twitter/X',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // Portfolio Banner Image
            ->add('portfolioCoverImage', FileType::class, [
                'label' => 'Portfolio Banner Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '5M',
                        'maxWidth' => 1600,
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, or WebP)',
                        'maxWidthMessage' => 'Portfolio banner image width must not exceed 1600px. Your image is {{ width }}px wide.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/jpeg,image/png,image/jpg,image/webp',
                ],
                'help' => 'Max 5MB. Image width must not exceed 1600px. Supported formats: JPEG, PNG, WebP. Recommended dimensions: 1400px width x 750px height.',
            ])
            ->add('portfolioCoverAltText', TextareaType::class, [
                'label' => 'Portfolio Banner Image Description',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 150])],
                'attr' => ['class' => 'form-control', 'rows' => 2],
                'help' => 'Short description of your portfolio banner image (for accessibility) - 150 characters max.',
            ])

            // Specialities
            ->add('specialities', EntityType::class, [
                'class' => Speciality::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Select your photography specialities (you can select multiple options by holding Ctrl or Cmd key).',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photographer::class,
        ]);
    }
}

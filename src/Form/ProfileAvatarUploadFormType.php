<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileAvatarUploadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('avatar', FileType::class, [
            'label' => false,
            'mapped' => false,
            'required' => true,
            'attr' => [
                'class' => 'input input--dark',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please select an image',
                ]),
                new File([
                    'maxSize' => '3M',
                    'maxSizeMessage' => 'The file is too large. Maximum allowed size is 3MB.',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP).',
                ]),
            ],
        ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('currentPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Current password',
            'attr' => [
                'autocomplete' => 'current-password',
                'placeholder' => 'Current password',
            ],
            'constraints' => [
                new NotBlank(message: 'Please enter your current password'),
            ],
        ]);

        $builder->add('newPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'The password fields must match.',
            'first_options'  => [
                'label' => 'New password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'New password',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please enter a new password'),
                    new Length(
                        min: 12,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        max: 4096,
                    ),
                    new Regex(
                        pattern: '/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}$/',
                        message: 'Password must contain at least one uppercase letter, one number, and one special character.',
                    ),
                ],
            ],
            'second_options' => [
                'label' => 'Confirm new password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Confirm new password',
                ],
            ],
        ]);
    }
}
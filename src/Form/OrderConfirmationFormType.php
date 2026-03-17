<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class OrderConfirmationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acceptTerms', CheckboxType::class, [
                'label' => "I agree to the terms and conditions* - AJOUTER LIEN",
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must accept the terms and conditions.',
                    ]),
                ],
            ])

            ->add('note', TextareaType::class, [
                'label' => 'Note (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add a note for the photographer...',
                    'rows' => 4,
                ],
            ])

            ->add('confirm', SubmitType::class, [
                'label' => 'Confirm order',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

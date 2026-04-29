<?php

namespace App\Form;

use App\Entity\ServiceProposal;
use App\Entity\Tax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Speciality;


class ServiceProposalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
            'attr' => [
                'placeholder' => 'Enter a title for your proposal',
            ],
        ])
        ->add('message', TextareaType::class, [
            'label' => 'Message',
            'required' => true,
            'attr' => [
                'placeholder' => 'Write a description of your proposal',
                'rows' => 5,
            ],
        ])
        ->add('price_exclu_tax', MoneyType::class, [
            'label' => 'Price (excl. tax)',
            'required' => true,
            'currency' => '',
            'scale' => 2,
            'attr' => [
                'placeholder' => 'Enter the price without tax',
            ],
        ])
        ->add('tax', EntityType::class, [
            'class' => Tax::class,
            'label' => 'Tax',
            'choice_label' => 'name',
            'required' => true,
            'placeholder' => 'Select a tax',
            'choices' => $options['active_taxes'],
        ])
        ->add('expiration_date', DateTimeType::class, [
            'label' => 'Expiration date',
            'widget' => 'single_text',
            'required' => true,
            'constraints' => [
                new Assert\GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'Expiration date must be today or later.',
                ]),
            ],
            'attr' => [
                'min' => (new \DateTime())->format('Y-m-d'),
            ],
        ])

        // NEW FIELDS

        ->add('serviceDate', DateType::class, [
            'label' => 'Service date',
            'widget' => 'single_text',
            'required' => true,
            'constraints' => [
                new Assert\GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'Service date must be today or later.',
                ]),
            ],
            'attr' => [
                'min' => (new \DateTime())->format('Y-m-d'),
            ],
        ])
        ->add('startAt', TimeType::class, [
            'label' => 'Start time',
            'widget' => 'single_text',
            'required' => true,
        ])
        ->add('endAt', TimeType::class, [
            'label' => 'End time',
            'widget' => 'single_text',
            'required' => true,
        ])
        ->add('location', TextType::class, [
            'label' => 'Location',
            'required' => true,
            'attr' => [
                'placeholder' => 'Enter location',
            ],
        ])
        ->add('editedPhotoCount', IntegerType::class, [
            'label' => 'Edited photos included',
            'required' => true,
            'constraints' => [
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'Edited photo count must be 0 or higher.',
                ]),
            ],
            'attr' => [
                'min' => '0',
            ],
        ])
        ->add('deliveryDelay', IntegerType::class, [
            'label' => 'Delivery delay (days)',
            'required' => true,
            'constraints' => [
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'Delivery delay must be 0 or higher.',
                ]),
            ],
            'attr' => [
                'min' => '0',
            ],
        ])
        ->add('speciality', EntityType::class, [
            'class' => Speciality::class,
            'label' => 'Speciality',
            'choice_label' => 'name',
            'required' => true,
            'placeholder' => 'Select a speciality',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceProposal::class,
            'active_taxes' => [],
        ]);
    }
}

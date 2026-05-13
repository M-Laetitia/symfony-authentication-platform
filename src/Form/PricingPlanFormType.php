<?php

namespace App\Form;

use App\Entity\PricingPlan;
use App\Enum\PricingPlanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PricingPlanFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('planType', ChoiceType::class, [
                'choices' => [
                    'Basic' => PricingPlanType::BASIC,
                    'Standard' => PricingPlanType::STANDARD,
                    'Premium' => PricingPlanType::PREMIUM,
                ],
                'label' => 'Plan Type',
                'disabled' => $options['is_edit'],
                'help' => 'Select the pricing plan tier',
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Price',
                'help' => 'Amount in EUR',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 3],
                'help' => 'Brief description of the plan',
            ])
            // ->add('duration', TextType::class, [
            //     'label' => 'Duration',
            //     'required' => false,
            //     'help' => 'e.g., "2 hours", "1 day", "1 week"',
            // ])
            ->add('whatIncluded', TextareaType::class, [
                'label' => 'What\'s Included',
                'attr' => ['rows' => 5, 'placeholder' => "10 photos\nEditing\nGallery access"],
                'help' => 'Enter each item on a new line',
                'mapped' => false,
            ])
            ->add('additionnalInfos', TextareaType::class, [
                'label' => 'Additional Information',
                'attr' => ['rows' => 3, 'placeholder' => "Travel included\nMultiple locations\nCutting edge equipment"],
                'required' => false,
                'help' => 'Enter each info on a new line',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PricingPlan::class,
            'is_edit' => false,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\ServiceProposal;
use App\Entity\Tax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

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
            // Prix HT
            ->add('price_exclu_tax', MoneyType::class, [
                'label' => 'Price (excl. tax)',
                'required' => true,
                'currency' => 'EUR',
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

<?php

namespace App\Form;

use Psr\Log\LoggerInterface;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactFormType extends HoneyPotType
{

    public function __construct(LoggerInterface $honeyPotLogger,RequestStack $requestStack)
     {
        parent::__construct($honeyPotLogger, $requestStack);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options); 

        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Name',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('emailFrom', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email address',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Subject',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Message',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'I agree with <a href="/terms">terms</a> and <a href="/privacy">privacy policy</a>.',
                'label_html' => true,
                'constraints' => [
                    new IsTrue(message: 'You should agree to our terms.'),
                ],
            ])
            ->add('submittedAt', HiddenType::class, [
                'mapped' => false,
                'data' => time(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact_form',
            'honeypot_field_1' => 'phone_number', 
            'honeypot_field_2' => 'company',
            'form_type' => 'contact',
        ]);
    }
}

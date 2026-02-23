<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactFormType extends HoneyPotType
{
    private Security $security;

    public function __construct(Security $security,LoggerInterface $honeyPotLogger,RequestStack $requestStack)
     {
        $this->security = $security;
        parent::__construct($honeyPotLogger, $requestStack);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options); 

        $builder
            ->add('name', TextType::class, ['label' => 'Your name'])
            ->add('emailFrom', EmailType::class, ['label' => 'Your email'])
            ->add('subject', TextType::class, ['label' => 'Subject'])
            ->add('message', TextareaType::class, ['label' => 'Message'])
            ->add('send', SubmitType::class, ['label' => 'Send'])
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

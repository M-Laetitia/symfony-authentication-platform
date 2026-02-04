<?php

namespace App\Form\FormExtension;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use App\EventSubscriber\HoneyPotSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoneyPotType extends AbstractType
{
    private LoggerInterface $honeyPotLogger;
    private RequestStack $requestStack;


    public function __construct(LoggerInterface $honeyPotLogger, RequestStack $requestStack)
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add($options['honeypot_field_1'], TextType::class, $this->setHoneyPotConfiguration())
        ->add($options['honeypot_field_2'], TextType::class, $this->setHoneyPotConfiguration())
        ->addEventSubscriber(new HoneyPotSubscriber($this->honeyPotLogger, $this->requestStack, $options['honeypot_field_1'], $options['honeypot_field_2']))
    ;
    }

    protected function setHoneyPotConfiguration(): array
    {
        return [
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => '-1'
            ],
            // 'data' => 'fake data', // WARNING: DELETE THIS LINE AFTER TESTS !
            'mapped' => false,
            'required' => false
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'honeypot_field_1' => 'phone',
            'honeypot_field_2' => 'faxNumber',
        ]);
    }
}
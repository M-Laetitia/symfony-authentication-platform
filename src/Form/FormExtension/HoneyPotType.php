<?php

namespace App\Form\FormExtension;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use App\EventSubscriber\HoneyPotSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HoneyPotType extends AbstractType
{
    private LoggerInterface $honeyPotLogger;
    
    private RequestStack $requestStack;

    protected const DELICIOUS_HONEY_CANDY_FOR_BOT = "phone";

    protected const FABULOUS_HONEY_CANDY_FOR_BOT = "faxNumber";

    public function __construct(LoggerInterface $honeyPotLogger, RequestStack $requestStack)
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::DELICIOUS_HONEY_CANDY_FOR_BOT, TextType::class, $this->setHoneyPotConfiguration())
            ->add(self::FABULOUS_HONEY_CANDY_FOR_BOT, TextType::class, $this->setHoneyPotConfiguration())
            ->addEventSubscriber(new HoneyPotSubscriber($this->honeyPotLogger, $this->requestStack))
        ;
    }

    protected function setHoneyPotConfiguration(): array
    {
        return [
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => '-1'
            ],
            'data' => 'fake data', // WARNING: DELETE THIS LINE AFTER TESTS !
            'mapped' => false,
            'required' => false
        ];
    }
}
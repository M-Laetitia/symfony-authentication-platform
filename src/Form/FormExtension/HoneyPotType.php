<?php

namespace App\Form\FormExtension;

class HoneyPotType extends AbstractType 
{
    private LoggerInterface $logger;
    private RequestStack $requestStack;

    public function __construct ( LoggerInterface $logger, RequestStack $requestStack ) {
        $this->logger = $logger;
        $this->requestStack = $requestStack
    }

    protected const DELICIOUS_HONEY_CANDY_FOR_BOT = "phone";
    protected const FABULOUS_HONEY_CANDY_FOR_BOT = "faxNumber";

    public function buildForm (FormBuilderInterface $builder, array $options) {
        $builder->add(self::DELICIOUS_HONEY_CANDY_FOR_BOT, TextType::class)
    }
}
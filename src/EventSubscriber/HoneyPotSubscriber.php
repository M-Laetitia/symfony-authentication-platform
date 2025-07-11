<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HoneyPotSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $honeyPotLogger;
    
    private RequestStack $requestStack;

    public function __construct(LoggerInterface $honeyPotLogger, RequestStack $requestStack)
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array {

        // lors du subscribe - donc l'event pre_submit est fired > on appelle en callback checkHoneyJar > checkHoneyJar prend un formEvents > dans cet form event on récupère les datas qui ont été saisies
        return [
            FormEvents::PRE_SUBMIT => 'checkHoneyJar'
        ];
    }

    public function checkHoneyJar(FormEvent $event): void {

        // on récupère la request courrante (pour l'adresse ip)
        $request = $this->requestStack->getCurrentRequest();

        if(!$request) {
            return;
        }
        // dd($event);

        // $form = $event->getForm();
        // on récupère les datas
        $data = $event->getData();

        if(!array_key_exists('phone', $data) || !array_key_exists('faxNumber', $data)) {
            throw new HttpException(400, 'Go away to my form');
        }

        // destructuring
        [
            'phone' => $phone,
            'faxNumber' => $faxNumber
        ] = $data;
        
        // if($date['phone'])
        if($phone !== '' || $faxNumber !== '') {
            $this->honeyPotLogger->error("Potential spam attempt from a robot at the following ip address: '{$request->getClientIp()}'");
            $this->honeyPotLogger->info("The data in the phone field contained '{$phone}', and the fax field contained '{$faxNumber}'");
            
            throw new HttpException(403, 'Go away to my form, bot !');
        }
    }
}
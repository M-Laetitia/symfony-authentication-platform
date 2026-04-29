<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use App\Service\MailerService;
use App\Service\CommentSecurityService;


class ContactFormHandler
{
    public function __construct(
        private MailerService $mailerService,
        private CommentSecurityService $commentSecurityService
    ) {}

    public function handle(FormInterface $form, Request $request): array
    {
        $form->handleRequest($request);
  
        if (!$form->isSubmitted() || !$form->isValid()) {
            return ['success' => false];
        }

        $data = $form->getData();

        $submittedAt = (int)$form->get('submittedAt')->getData();
        $timeCheck = $this->commentSecurityService->checkSubmissionTime($submittedAt, $request);

        if (!$timeCheck['valid']) {
            return [
                'success' => false,
                'error' => $timeCheck['status']
            ];
        }

        $this->mailerService->sendContactEmail(
            $data['name'],
            $data['emailFrom'],
            $data['message'],
            $data['subject']
        );

        return ['success' => true];
    }
}
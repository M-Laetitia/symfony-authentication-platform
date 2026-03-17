<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class UserEmailVerificationService
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private UserRepository $userRepository,
        private TranslatorInterface $translator,
    ) {}

    public function findUserFromRequest(Request $request): ?User
    {
        $id = $request->query->get('id');
        
        if (null === $id) {
            return null;
        }

        return $this->userRepository->find($id);
    }

    public function verifyEmail(Request $request, User $user): array
    {
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            
            return [
                'success' => true,
                'message' => 'Your email address has been verified.',
                'flash_type' => 'success'
            ];
        } catch (VerifyEmailExceptionInterface $exception) {
            return [
                'success' => false,
                'message' => $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'),
                'flash_type' => 'verify_email_error'
            ];
        }
    }
}
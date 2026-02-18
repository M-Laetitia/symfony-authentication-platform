<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProfileFormType;
use App\Form\ProfileAvatarUploadFormType;
use App\Form\ProfileChangePasswordFormType;
use App\Enum\MediaType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;
use App\Service\MediaUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request,  EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MediaUploader $mediaUploader): Response
    {
        // Récupère l'utilisateur connecté via AbstractController
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Si aucun utilisateur => redirection vers login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // if (!$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException('Accès refusé : rôle insuffisant.');
        // }

        //^ FORM INFO
        $profilInfoForm = $this->createForm(ProfileFormType::class, $user);
        $profilInfoForm->handleRequest($request);
        if ($profilInfoForm->isSubmitted() && $profilInfoForm->isValid()) {

            $entityManager->flush();
    
            $this->addFlash('success', 'Profile updated successfully.');
    
            return $this->redirectToRoute('app_profile');
        }
        
        //^ Form password
        $formPassword = $this->createForm(ProfileChangePasswordFormType::class);
        $formPassword->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {

            $currentPassword = $formPassword->get('currentPassword')->getData();
            $newPassword = $formPassword->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $formPassword->get('currentPassword')->addError(new FormError('Current password is incorrect.'));
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', 'Password changed successfully.');
                return $this->redirectToRoute('app_profile');
            }
        }

        //^ Avatar upload
        $formAvatar = $this->createForm(ProfileAvatarUploadFormType::class);
        $formAvatar->handleRequest($request);
    
        if ($formAvatar->isSubmitted() && $formAvatar->isValid()) {
            /** @var UploadedFile $avatarFile */
            $avatarFile = $formAvatar->get('avatar')->getData();
            if ($avatarFile instanceof UploadedFile) {
                // Delete the old image
                if ($user->getAvatar()) {
                    @unlink($this->getParameter('uploads_directory') . '/' . $user->getAvatar()->getPath());
                }

    
                $media = $mediaUploader->upload(
                    $avatarFile,
                    $user->getUsername() . 'avatar ',
                    'Avatar',
                    MediaType::AVATAR,
                    'avatar',
                );
    
                $user->setAvatar($media);
                $entityManager->flush();
    
                $this->addFlash('success', 'Avatar updated successfully.');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'profilInfoForm' => $profilInfoForm->createView(),
            'formPassword' => $formPassword->createView(),
            'formAvatar' => $formAvatar->createView(),
        ]);
    }
}
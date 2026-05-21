<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setRoles(['ROLE_USER']);
            $user->setStatus('active');
            $user->setIsVerified(false);

            $verificationToken = $emailVerificationService->generateVerificationToken();
            $user->setVerificationToken($verificationToken);

            $entityManager->persist($user);
            $entityManager->flush();

            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            try {
                $emailVerificationService->sendVerificationEmail($user, $verificationUrl);
                $this->addFlash(
                    'success',
                    '✔ Registration successful! Please check your email to verify your account before logging in.'
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'warning',
                    '✔ Account created but we could not send the verification email. Please contact support.'
                );
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify-email', name: 'app_verify_email')]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            $this->addFlash('error', 'Invalid verification link.');
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy([
            'verificationToken' => $token,
        ]);

        if (!$user) {
            $this->addFlash('error', 'Invalid or expired verification link.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', 'Your account is already verified. Please log in.');
            return $this->redirectToRoute('app_login');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $entityManager->flush();

        $this->addFlash('success', '✔ Email verified! You can now log in.');
        return $this->redirectToRoute('app_login');
    }
}
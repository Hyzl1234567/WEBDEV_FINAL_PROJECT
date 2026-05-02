<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailVerificationService
{
    public function __construct(private MailerInterface $mailer) {}

    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function sendVerificationEmail(object $user, string $verificationUrl): void
    {
        $email = (new Email())
            ->from('estrabelahyzlann0@gmail.com')
            ->to($user->getEmail())
            ->subject('Verify your EcoBrew Café account')
            ->html('
                <div style="font-family:sans-serif;max-width:520px;margin:auto;padding:32px;
                            border:1px solid #e0e0e0;border-radius:12px;">
                    <h2 style="color:#1D4A23;">☕ Welcome to EcoBrew Café!</h2>
                    <p>Hi <strong>' . htmlspecialchars($user->getFullName()) . '</strong>,</p>
                    <p>Thanks for registering! Please verify your email address by clicking the button below.</p>
                    <a href="' . $verificationUrl . '"
                       style="display:inline-block;margin:24px 0;padding:12px 28px;
                              background:#1D4A23;color:white;border-radius:8px;
                              text-decoration:none;font-weight:600;">
                        Verify My Account
                    </a>
                    <p style="color:#888;font-size:13px;">
                        This link expires in 24 hours.<br>
                        If you did not register with EcoBrew, you can safely ignore this email.
                    </p>
                </div>
            ');

        $this->mailer->send($email);
    }
}
<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailVerificationService
{
    public function __construct(private MailerInterface $mailer) {}

    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function sendEmail(string $toEmail, string $token)
    {
        $link = "http://127.0.0.1:8000/verify-email?token=" . $token;

        $email = (new Email())
            ->from('estrabelahyzlann0@gmail.com')
            ->to($toEmail)
            ->subject('Verify your account')
            ->text("Click this link to verify: " . $link);

        $this->mailer->send($email);
    }
}
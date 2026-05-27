<?php

namespace App\Service;

use App\Entity\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private ?Messaging $messaging = null;

    public function __construct(
        private readonly string $credentialsPath,
        private readonly LoggerInterface $logger,
    ) {
        if (file_exists($this->credentialsPath)) {
            try {
                $this->messaging = (new Factory())
                    ->withServiceAccount($this->credentialsPath)
                    ->createMessaging();
            } catch (\Throwable $e) {
                $this->logger->error('Failed to initialize Firebase Messaging: ' . $e->getMessage());
            }
        } else {
            $this->logger->error('Firebase credentials file not found: ' . $this->credentialsPath);
        }
    }

    /**
     * Send a notification to a specific FCM token
     */
    public function sendToToken(
        string $fcmToken,
        string $title,
        string $body,
        array $data = []
    ): bool {
        if (null === $this->messaging) {
            $this->logger->error('Firebase Messaging not configured');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification([
                    'title' => $title,
                    'body'  => $body,
                ])
                ->withData($data);

            $result = $this->messaging->send($message);

            $this->logger->info('Notification sent successfully', [
                'token'  => substr($fcmToken, 0, 20) . '...',
                'title'  => $title,
                'result' => $result,
            ]);

            return true;

        } catch (\Throwable $e) {
            $this->logger->error('Failed to send notification to token', [
                'token'     => substr($fcmToken, 0, 20) . '...',
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return false;
        }
    }

    /**
     * Send a notification to a user (uses their stored FCM token)
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): bool {
        $fcmToken = $user->getFcmToken();

        if (empty($fcmToken)) {
            $this->logger->warning('User has no FCM token', [
                'userId' => $user->getId(),
                'email'  => $user->getEmail(),
            ]);
            return false;
        }

        return $this->sendToToken($fcmToken, $title, $body, $data);
    }

    /**
     * Send a notification to multiple users
     */
    public function sendToUsers(
        array $users,
        string $title,
        string $body,
        array $data = []
    ): array {
        $results = [
            'sent'    => 0,
            'failed'  => 0,
            'skipped' => 0, // Users with no FCM token
        ];

        foreach ($users as $user) {
            if (!$user instanceof User) {
                $this->logger->warning('Invalid user object in sendToUsers');
                $results['failed']++;
                continue;
            }

            $fcmToken = $user->getFcmToken();

            if (empty($fcmToken)) {
                $results['skipped']++;
                continue;
            }

            if ($this->sendToToken($fcmToken, $title, $body, $data)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        $this->logger->info('Batch notification sending complete', $results);

        return $results;
    }

    /**
     * Check if Firebase Messaging is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->messaging !== null;
    }
}

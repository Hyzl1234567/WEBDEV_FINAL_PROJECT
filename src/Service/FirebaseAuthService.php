<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\Token\ExpiredToken;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FirebaseAuthService
{
    private Auth $auth;

    public function __construct(
        private readonly string $credentialsPath,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
        if (!file_exists($this->credentialsPath)) {
            throw new \RuntimeException('Firebase credentials file not found: ' . $this->credentialsPath);
        }

        $this->auth = (new Factory())
            ->withServiceAccount($this->credentialsPath)
            ->createAuth();
    }

    public function verifyToken(string $idToken): ?array
    {
        try {
            $this->logger->info('Attempting to verify Firebase ID token');

            // Cache key based on token hash — avoids re-fetching Google public keys
            // on every request. Cached for 5 minutes (tokens expire in 1 hour anyway).
            $cacheKey = 'firebase_token_' . md5($idToken);

            $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($idToken) {
                $item->expiresAfter(300); // 5 minutes

                $verifiedIdToken = $this->auth->verifyIdToken($idToken);

                $this->logger->info('Firebase ID token verified successfully', [
                    'uid'   => $verifiedIdToken->claims()->get('sub'),
                    'email' => $verifiedIdToken->claims()->get('email'),
                ]);

                return [
                    'uid'   => $verifiedIdToken->claims()->get('sub'),
                    'email' => $verifiedIdToken->claims()->get('email'),
                    'name'  => $verifiedIdToken->claims()->get('name') ?? null,
                    'photo' => $verifiedIdToken->claims()->get('picture') ?? null,
                ];
            });

            return $result;

        } catch (ExpiredToken $e) {
            $this->logger->warning('Firebase ID token is expired', [
                'error' => $e->getMessage(),
            ]);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('Failed to verify Firebase ID token', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return null;
        }
    }
}
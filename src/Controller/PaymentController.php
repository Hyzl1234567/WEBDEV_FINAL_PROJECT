<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends AbstractController
{
    #[Route('/api/payment/create-intent', name: 'api_payment_create_intent', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createIntent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? null; // amount in centavos (PHP) or cents (USD)

        if (!$amount || $amount <= 0) {
            return new JsonResponse(['error' => 'Invalid amount'], 400);
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $intent = PaymentIntent::create([
            'amount'   => (int) $amount,   // e.g. 50000 = ₱500.00
            'currency' => 'php',           // change to 'usd' if needed
            'metadata' => [
                'user_id' => $this->getUser()->getId(),
            ],
        ]);

        return new JsonResponse([
            'clientSecret' => $intent->client_secret,
        ]);
    }

    #[Route('/api/payment/webhook', name: 'api_payment_webhook', methods: ['POST'])]
public function webhook(Request $request, EntityManagerInterface $em): JsonResponse
{
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    $payload = $request->getContent();
    $sigHeader = $request->headers->get('stripe-signature');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sigHeader, $_ENV['STRIPE_WEBHOOK_SECRET']
        );
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Invalid signature'], 400);
    }

    if ($event->type === 'payment_intent.succeeded') {
        $intent = $event->data->object;
        $userId = $intent->metadata->user_id;

        // TODO: find the order by user_id and mark it as paid
        // $order = $em->getRepository(Order::class)->findLatestByUser($userId);
        // $order->setStatus('paid');
        // $em->flush();
    }

    return new JsonResponse(['status' => 'ok']);
}
}
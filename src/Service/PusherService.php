<?php

// src/Service/PusherService.php

namespace App\Service;

use Pusher\Pusher;

class PusherService
{
    private Pusher $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            $_ENV['PUSHER_KEY'],
            $_ENV['PUSHER_SECRET'],
            $_ENV['PUSHER_APP_ID'],
            [
                'cluster'   => $_ENV['PUSHER_CLUSTER'],
                'useTLS'    => true,
            ]
        );
    }

    // ── Trigger any event on any channel ─────────────────────────────────────

    public function trigger(string $channel, string $event, array $data): void
    {
        $this->pusher->trigger($channel, $event, $data);
    }

    // ── Order Events ──────────────────────────────────────────────────────────

    // Fired when a new order is placed
    public function orderPlaced(array $order): void
    {
        // Notify staff on the staff channel
        $this->trigger('staff-orders', 'order.placed', [
            'order'    => $order,
            'message'  => 'New order received!',
        ]);

        // Notify the specific customer on their private channel
        $this->trigger(
            'customer-' . $order['customer']['id'],
            'order.placed',
            ['order' => $order]
        );
    }

    // Fired when order status is updated (e.g. Pending → Processing)
    public function orderStatusUpdated(array $order): void
    {
        // Notify staff
        $this->trigger('staff-orders', 'order.status_updated', [
            'order'   => $order,
            'message' => 'Order #' . $order['id'] . ' is now ' . $order['status'],
        ]);

        // Notify the specific customer
        $this->trigger(
            'customer-' . $order['customer']['id'],
            'order.status_updated',
            ['order' => $order]
        );
    }

    // ── Stock Events ──────────────────────────────────────────────────────────

    // Fired when product stock changes
    public function stockUpdated(int $productId, string $productName, int $newQuantity): void
    {
        $this->trigger('products', 'stock.updated', [
            'product_id' => $productId,
            'name'       => $productName,
            'quantity'   => $newQuantity,
        ]);
    }
}
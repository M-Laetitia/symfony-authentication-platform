<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(
        int    $amount,     
        string $currency,
        string $description,
        string $successUrl,
        string $cancelUrl,
        int    $orderId,
    ): Session {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $amount,
                    'product_data' => ['name' => $description],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'billing_address_collection' => 'required',
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
            'metadata'    => ['order_id' => $orderId],
        ]);
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }
}
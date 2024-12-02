<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use Illuminate\Support\Facades\Cache;
use Stripe\StripeClient;

class StripeService
{
    public $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    public function createCheckoutSession(array $data)
    {
        $name = auth()->user()->first_name . "'s subscription for $" . $data['price'];

        $checkoutStripeSession = $this->stripe->checkout->sessions->create([
            'customer_email'       => auth()->user()->email,
            'success_url'          => route('subscription.success'),
            'cancel_url'           => route('subscription.cancel'),
            'metadata'             => ['data' => json_encode($data)],
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'product_data' => [
                            'name'   => $name,
                            'images' => ['https://files.stripe.com/links/MDB8YWNjdF8xS2Vld2tLY2lOUHR6b1RpfGZsX3Rlc3RfYkZzRkM5UkxYMTFRUHFTQ21mNWM2ZFQx00GF3YS6op'],
                            'metadata' => [
                                'pro_id' => "USD" . ((int) ($data['price'] * 100))
                            ]
                        ],
                        'unit_amount' => ((int) ($data['price'] * 100)),
                        'currency'    => 'usd',
                    ],
                    'quantity' => 1,
                ]
            ],
        ]);

        Cache::set('stripe_checkout_id', $checkoutStripeSession->id);
        return $checkoutStripeSession;
    }

    public function handlePaymentSuccess(string $stripeSessionId)
    {
        if (empty($stripeSessionId)) {
            throw new BadRequestException('Stripe session ID is required.');
        }

        $session  = $this->stripe->checkout->sessions->retrieve($stripeSessionId, []);
        $metadata = json_decode($session->metadata->data);

        if ($session->payment_status == "paid") {
            Cache::forget('stripe_checkout_id');
            return $metadata;
        }
        throw new BadRequestException('Payment failed.');
    }
}

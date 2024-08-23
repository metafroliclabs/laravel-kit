<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class PaymentService
{
    private $stripe;
    private $type;
    private $amount;

    private function __construct($type, $amount)
    {
        self::$type   = $type;
        self::$amount = $amount;
        self::$stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    public function initiate($type = 'order', $amount = 0)
    {
        try {
            return new self($type, $amount);

        } catch (Exception $e) {
            Log::info('Error catch in ' . __FUNCTION__ . ' function');
            Log::error($e->getMessage());
            return customResponse(false, $e->getMessage(), 500);
        }
    }

    public function handlePayment($request)
    {
        try {
            if (!isset(self::$type) || !isset(self::$amount)) {
                return customResponse(false, 'Payment type and amount required.', 400);
            }

            $name = auth()->user()->first_name ."'s ". self::$type ." for $". self::$amount;

            // $product = $this->stripe->products->create([
            //     // 'id'=> $uniqueProductId,
            //     'name'        => $name,
            //     'description' => $name,
            //     'active'      => 'true',
            //     'images'      => [
            //         'https://files.stripe.com/links/MDB8YWNjdF8xS2Vld2tLY2lOUHR6b1RpfGZsX3Rlc3RfYkZzRkM5UkxYMTFRUHFTQ21mNWM2ZFQx00GF3YS6op'
            //     ],
            // ]);
            // $price = $this->stripe->prices->create([
            //     'product'      => $product['id'],
            //     'unit_amount'  => ((int) (self::$amount * 100)),
            //     'currency'     => 'usd',
            //     'tax_behavior' => 'exclusive',
            // ]);
            $checkoutStripeSession = $this->stripe->checkout->sessions->create([
                'customer_email'       => auth()->user()->email,
                'success_url'          => route(self::$type . '.success'),
                'cancel_url'           => route(self::$type . '.cancel'),
                'payment_method_types' => ['card'],
                'metadata'             => ['service_message' => json_encode($request)],
                'mode'                 => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'product_data' => [
                            'name'   => $name,
                            'images' => ['https://files.stripe.com/links/MDB8YWNjdF8xS2Vld2tLY2lOUHR6b1RpfGZsX3Rlc3RfYkZzRkM5UkxYMTFRUHFTQ21mNWM2ZFQx00GF3YS6op'],
                            'metadata' => [
                                'pro_id' => "USD" . ((int) (self::$amount * 100))
                            ]
                        ],
                        'unit_amount' => ((int) (self::$amount * 100)),
                        'currency'    => 'usd',
                    ],
                    'quantity' => 1,
                ]],
            ]);

            Cache::set('stripe_checkout_id', $checkoutStripeSession->id);
            return customResponse(true, 'Payment initiated.', 200, $checkoutStripeSession);

        } catch (Exception $e) {
            Log::info('Error catch in ' . __FUNCTION__ . ' function');
            Log::error($e->getMessage());
            return customResponse(false, $e->getMessage(), 500);
        }
    }

    public function handlePaymentSuccess($stripeSessionId)
    {
        try {
            if (isset($stripeSessionId)) {
                $stripe_response = $this->stripe->checkout->sessions->retrieve($stripeSessionId, []);
                $service_message = json_decode($stripe_response->metadata->service_message);

                if ($stripe_response->payment_status == "paid") {
                    Cache::forget('stripe_checkout_id');
                    return customResponse(true, 'Payment successful.', 200, $service_message);
                }
                return customResponse(false, 'Payment failed.', 400);
            }
            return customResponse(false, 'Stripe session id required.', 422);

        } catch (Exception $e) {
            Log::info('Error catch in ' . __FUNCTION__ . ' function');
            Log::error($e->getMessage());
            return customResponse(false, $e->getMessage(), 500);
        }
    }
}
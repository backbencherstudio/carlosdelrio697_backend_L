<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\Service;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required',
            'service_id'        => 'required|exists:services,id',
            'customer_name'     => 'required|string',
            'customer_email'    => 'required|email',
            'state'             => 'required|string',
        ]);

        $service = Service::findOrFail($request->service_id);
        $price = $service->price;
        $amountInCents = $price * 100;

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);
            $cardBrand = $paymentMethod->card->brand;
            $cardLast4 = $paymentMethod->card->last4;

            $lastOrder = Order::latest()->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number'          => $orderNumber,
                'service_id'            => $service->id,
                'service_name'          => $service->title,
                'customer_name'         => $request->customer_name,
                'customer_email'        => $request->customer_email,
                'state'                 => $request->state,
                'amount'                => $price,
                'status'                => 'Completed',
                'card_brand'            => ucfirst($cardBrand),
                'card_last4'            => $cardLast4,
                'document_status'       => 'Ready',
                'stripe_transaction_id' => $intent->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order processed successfully!',
                'order_details' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

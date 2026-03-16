<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Service;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        // ১. ভ্যালিডেশন
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'payment_method_id' => 'required',
            'customer_email' => 'nullable|email', // গ্রাহকের ইমেল যদি পাঠান
            'customer_name' => 'nullable|string', // গ্রাহকের নাম যদি পাঠান
        ]);

        // ২. ডাটাবেস থেকে ডাইনামিক প্রাইস
        $service = Service::findOrFail($request->service_id);
        $amountInCents = $service->price * 100;

        // ৩. Stripe Secret Key
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // ৪. PaymentIntent তৈরি
            $intent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd', // আপনার কারেন্সি
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'description' => "Payment for Service: " . $service->name,
                // 'return_url' => route('payment.success'), // যদি পেমেন্ট শেষে কোনো পেজে রিডাইরেক্ট করতে চান
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'metadata' => [ // stripe dashboard-এ দেখার জন্য
                    'service_id' => $service->id,
                    'customer_email' => $request->customer_email ?? 'N/A',
                    'customer_name' => $request->customer_name ?? 'N/A',
                ]
            ]);

            // ৫. ডাটাবেসে পেমেন্ট রেকর্ড সেভ করা
            Payment::create([
                'service_id' => $service->id,
                'amount' => $service->price,
                'transaction_id' => $intent->id,
                'status' => 'completed', // Stripe confirmation automatically makes it completed
                'customer_email' => $request->customer_email,
                'customer_name' => $request->customer_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment Successful!',
                'transaction_id' => $intent->id // চাইলে ট্রানজেকশন আইডি ফেরত দিতে পারেন
            ]);
        } catch (\Stripe\Exception\CardErrorException $e) {
            // Handle card errors
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'card_error'
            ], 400);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle other Stripe API errors
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error'
            ], 500);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error'
            ], 500);
        }
    }
}

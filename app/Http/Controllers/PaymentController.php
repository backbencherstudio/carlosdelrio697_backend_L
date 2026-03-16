<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'state' => 'required|string',
        ]);

        $serviceName = "Real Estate Power of Attorney";
        $price = 150.00;
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
            $cardBrand = $paymentMethod->card->brand; // like: visa
            $cardLast4 = $paymentMethod->card->last4; // like: 4242

            $lastOrder = Order::latest()->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number'          => $orderNumber,
                'customer_name'         => $request->customer_name,
                'customer_email'        => $request->customer_email,
                'service_name'          => $serviceName,
                'state'                 => $request->state,
                'amount'                => $price,
                'status'                => 'Completed',
                'card_brand'            => ucfirst($cardBrand), // Visa
                'card_last4'            => $cardLast4,         // 4242
                'document_status'       => 'Ready',            // Ready
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

    public function getAdminOrders(Request $request)
    {
        $query = Order::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('stripe_transaction_id', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('state', 'LIKE', "%{$search}%")
                    ->orWhere('service_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != 'All') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_filter')) {
            if ($request->date_filter == 'this_month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            } elseif ($request->date_filter == 'last_month') {
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
            } elseif ($request->date_filter == 'this_year') {
                $query->whereYear('created_at', Carbon::now()->year);
            }
        }

        $orders = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function getOrderDetail($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $totalUniqueCustomers = Order::distinct('customer_email')->count('customer_email');
            $totalRevenue = Order::sum('amount');
            $avgSpent = $totalUniqueCustomers > 0 ? $totalRevenue / $totalUniqueCustomers : 0;

            $query = Order::select(
                'customer_email',
                DB::raw('MAX(customer_name) as customer_name'), // Get the latest name
                DB::raw('MAX(state) as state'),                // Get the latest state
                DB::raw('MIN(created_at) as joined'),          // First order date
                DB::raw('MAX(created_at) as last_activity'),   // Most recent order date
                DB::raw('COUNT(*) as total_orders'),           // Number of orders
                DB::raw('SUM(amount) as total_spent')          // Total money spent
            )
                ->groupBy('customer_email');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_email', 'LIKE', "%{$search}%");
                });
            }

            $customers = $query->orderBy('last_activity', 'desc')->paginate(10);

            $customers->getCollection()->transform(function ($customer) {
                return [
                    'name'           => $customer->customer_name,
                    'email'          => $customer->customer_email,
                    'state'          => $customer->state,
                    'joined'         => Carbon::parse($customer->joined)->format('F d, Y'),
                    'total_orders'   => $customer->total_orders,
                    'total_spent'    => number_format($customer->total_spent, 2),
                    'last_activity'  => Carbon::parse($customer->last_activity)->format('F d, Y'),
                ];
            });

            return response()->json([
                'success' => true,
                'metrics' => [
                    'total_customers' => $totalUniqueCustomers,
                    'avg_spent'       => number_format($avgSpent, 2)
                ],
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load customer list.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}

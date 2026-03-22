<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('state', 'like', "%$search%");
            });
        }

        $statsQuery = clone $query;

        $customers = $query->orderByDesc('id')->paginate($perPage);

        $totalCustomers = $statsQuery->count();
        $averageSpent = $statsQuery->avg('total_spent');

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',

            'stats' => [
                'total_customers' => $totalCustomers,
                'average_spent'   => round($averageSpent, 2),
            ],

            'data' => $customers
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getMetrics()
    {
        $totalRevenue = Order::sum('amount');

        $activeOrders = Order::count();

        $newCustomersWeek = Order::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])
            ->distinct('customer_email')
            ->count('customer_email');

        $documentsGenerated = Order::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => number_format($totalRevenue, 2),
                'active_orders' => $activeOrders,
                'new_customers_week' => $newCustomersWeek,
                'documents_generated' => $documentsGenerated,
            ]
        ]);
    }

    
}

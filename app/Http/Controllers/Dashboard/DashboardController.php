<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Key Metrics
        $total_paid = Sale::sum('pay_amount');
        $total_due = Sale::sum('due_amount');
        $complete_sales = Sale::where('sale_status', 'complete')->count();
        $pending_sales = Sale::where('sale_status', 'pending')->count();

        // 2. Today's Snapshot
        $today_sales = Sale::whereDate('created_at', \Carbon\Carbon::today())->sum('total');

        $top_products = DB::table('sale_details')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'products.image as product_image',
                'products.code as product_code',
                DB::raw('SUM(sale_details.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.image', 'products.code')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 4. Monthly Sales Data for Chart (Current Year)
        $monthly_sales = Sale::select(
            DB::raw('SUM(total) as total_amount'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_amount', 'month')
            ->toArray();

        // Fill missing months with 0
        $chart_data = [];
        for ($i = 1; $i <= 12; $i++) {
            $chart_data[] = $monthly_sales[$i] ?? 0;
        }

        // 5. Recent Transactions
        $recent_sales = Sale::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'total_paid' => $total_paid,
            'total_due' => $total_due,
            'complete_sales' => $complete_sales,
            'pending_sales' => $pending_sales,
            'today_sales' => $today_sales,
            'top_products' => $top_products,
            'chart_data' => json_encode($chart_data),
            'recent_sales' => $recent_sales,
        ]);
    }
}

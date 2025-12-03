<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * تقارير المبيعات
     */
    public function sales(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $baseQuery = Order::where('payment_status', 'paid');

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // Default to last 30 days
            $baseQuery->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Get sales grouped by date
        $sales = (clone $baseQuery)->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy(DB::raw('DATE(created_at)'), 'desc')
        ->get();

        // Get summary statistics
        $summary = [
            'total_orders' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->sum('total_amount'),
            'average_order_value' => (clone $baseQuery)->avg('total_amount') ?? 0,
        ];

        return Inertia::render('Admin/theme1/Reports/Sales', [
            'sales' => $sales,
            'summary' => $summary,
            'filters' => $request->only(['period', 'start_date', 'end_date']),
        ]);
    }

    /**
     * تقارير المنتجات
     */
    public function products(Request $request)
    {
        $type = $request->get('type', 'best_selling'); // best_selling, least_selling, out_of_stock

        $query = OrderItem::select(
            'product_id',
            'product_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('COUNT(DISTINCT order_id) as orders_count')
        )
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.payment_status', 'paid')
        ->groupBy('product_id', 'product_name');

        if ($type === 'best_selling') {
            $query->orderBy('total_quantity', 'desc');
        } elseif ($type === 'least_selling') {
            $query->orderBy('total_quantity', 'asc');
        }

        $products = $query->limit(50)->get();

        return Inertia::render('Admin/theme1/Reports/Products', [
            'products' => $products,
            'filters' => $request->only(['type']),
        ]);
    }

    /**
     * تقارير العملاء
     */
    public function customers(Request $request)
    {
        $type = $request->get('type', 'top_customers'); // top_customers, new_customers

        if ($type === 'top_customers') {
            $customers = User::where('user_type', 'client')
                ->select('users.*')
                ->selectSub(function ($query) {
                    $query->select(DB::raw('SUM(total_amount)'))
                        ->from('orders')
                        ->whereColumn('orders.user_id', 'users.id')
                        ->where('payment_status', 'paid');
                }, 'total_spent')
                ->selectSub(function ($query) {
                    $query->select(DB::raw('COUNT(*)'))
                        ->from('orders')
                        ->whereColumn('orders.user_id', 'users.id');
                }, 'orders_count')
                ->having('total_spent', '>', 0)
                ->orderBy('total_spent', 'desc')
                ->limit(50)
                ->get();
        } else {
            // New customers (last 30 days)
            $customers = User::where('user_type', 'client')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->withCount('orders')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        return Inertia::render('Admin/theme1/Reports/Customers', [
            'customers' => $customers,
            'filters' => $request->only(['type']),
        ]);
    }

    /**
     * تقارير الطلبات
     */
    public function orders(Request $request)
    {
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Order::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // Default to last 30 days
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $orders = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_orders' => $query->count(),
            'total_revenue' => $query->where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders' => $query->where('status', 'pending')->count(),
            'completed_orders' => $query->where('status', 'delivered')->count(),
        ];

        return Inertia::render('Admin/theme1/Reports/Orders', [
            'orders' => $orders,
            'summary' => $summary,
            'statuses' => Order::getStatuses(),
            'filters' => $request->only(['status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * تقارير الإيرادات
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Order::where('payment_status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // Default to last 12 months
            $query->where('created_at', '>=', Carbon::now()->subMonths(12));
        }

        // Get revenue grouped by month
        $revenue = (clone $query)->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('SUM(subtotal) as subtotal'),
            DB::raw('SUM(tax_amount) as tax'),
            DB::raw('SUM(shipping_amount) as shipping'),
            DB::raw('SUM(discount_amount) as discount'),
            DB::raw('COUNT(*) as orders_count')
        )
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), 'desc')
        ->get();

        // Get summary statistics
        $summary = [
            'total_revenue' => (clone $query)->sum('total_amount'),
            'total_orders' => (clone $query)->count(),
            'average_order_value' => (clone $query)->avg('total_amount') ?? 0,
            'total_tax' => (clone $query)->sum('tax_amount'),
            'total_shipping' => (clone $query)->sum('shipping_amount'),
            'total_discount' => (clone $query)->sum('discount_amount'),
        ];

        return Inertia::render('Admin/theme1/Reports/Revenue', [
            'revenue' => $revenue,
            'summary' => $summary,
            'filters' => $request->only(['period', 'start_date', 'end_date']),
        ]);
    }

    /**
     * تقارير المخزون
     */
    public function inventory(Request $request)
    {
        $type = $request->get('type', 'all'); // all, low_stock, out_of_stock, in_stock

        $query = Product::query();

        if ($type === 'low_stock') {
            $query->where('manage_stock', true)
                ->whereColumn('stock_quantity', '<=', DB::raw('10'))
                ->where('stock_quantity', '>', 0);
        } elseif ($type === 'out_of_stock') {
            $query->where(function($q) {
                $q->where('manage_stock', true)
                  ->where('stock_quantity', '<=', 0)
                  ->orWhere('in_stock', false);
            });
        } elseif ($type === 'in_stock') {
            $query->where('in_stock', true)
                ->where(function($q) {
                    $q->where('manage_stock', false)
                      ->orWhere(function($q2) {
                          $q2->where('manage_stock', true)
                            ->where('stock_quantity', '>', 0);
                      });
                });
        }

        $products = $query->with(['category', 'brand'])
            ->orderBy('stock_quantity', 'asc')
            ->paginate(50);

        $summary = [
            'total_products' => Product::count(),
            'in_stock' => Product::where('in_stock', true)->count(),
            'out_of_stock' => Product::where('in_stock', false)->orWhere(function($q) {
                $q->where('manage_stock', true)->where('stock_quantity', '<=', 0);
            })->count(),
            'low_stock' => Product::where('manage_stock', true)
                ->whereColumn('stock_quantity', '<=', DB::raw('10'))
                ->where('stock_quantity', '>', 0)
                ->count(),
        ];

        return Inertia::render('Admin/theme1/Reports/Inventory', [
            'products' => $products,
            'summary' => $summary,
            'filters' => $request->only(['type']),
        ]);
    }
}


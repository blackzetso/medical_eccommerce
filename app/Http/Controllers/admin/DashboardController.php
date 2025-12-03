<?php

namespace App\Http\Controllers\admin;

use Inertia\inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stats = [
            'total_products' => \App\Models\Product::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_clients' => \App\Models\User::where('user_type', 'client')->count(),
            'total_revenue' => \App\Models\Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            'completed_orders' => \App\Models\Order::where('status', 'delivered')->count(),
        ];

        // Get revenue chart data (last 7 days)
        $revenueData = \App\Models\Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'))
            ->orderBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), 'asc')
            ->get();

        $chartData = [
            'dates' => $revenueData->pluck('date')->toArray(),
            'revenue' => $revenueData->pluck('revenue')->toArray(),
        ];

        return inertia::render('Admin/theme1/Index', [
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

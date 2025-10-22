<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::where('is_deleted', false)->count();
        $totalCustomers = Customer::count();
        $totalUsers = User::count();

        $ordersByStatus = Order::where('is_deleted', false)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentOrders = Order::with(['customer', 'creator'])
            ->where('is_deleted', false)
            ->latest('order_date')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalCustomers',
            'totalUsers',
            'ordersByStatus',
            'recentOrders'
        ));
    }
}

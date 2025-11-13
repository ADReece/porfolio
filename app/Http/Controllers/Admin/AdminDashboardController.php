<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $newUsers30 = User::where('created_at', '>=', now()->subDays(30))->count();
        $activeSubscribers = User::whereHas('subscriptions', function($q){ $q->whereNull('ends_at'); })->count();
        $ordersLast30 = Order::where('created_at', '>=', now()->subDays(30))->count();
        $gmvLast30 = Order::where('status','completed')->where('created_at','>=',now()->subDays(30))->sum('total');

        return view('admin.dashboard', compact('totalUsers','newUsers30','activeSubscribers','ordersLast30','gmvLast30'));
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class AdminOrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(25);
        $totalCompleted = Order::where('status','completed')->sum('total');
        return view('admin.orders.index', compact('orders','totalCompleted'));
    }
}


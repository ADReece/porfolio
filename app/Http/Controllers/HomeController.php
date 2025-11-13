<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('active', true)
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('plans'));
    }

    public function terms()
    {
        return view('legal.terms');
    }

    public function privacy()
    {
        return view('legal.privacy');
    }

    public function sla()
    {
        return view('legal.sla');
    }

    public function about()
    {
        return view('about');
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionsController extends Controller
{
    public function index()
    {
        $subs = DB::table('subscriptions')
            ->join('users','subscriptions.user_id','=','users.id')
            ->select('subscriptions.*','users.email','users.name')
            ->orderByDesc('subscriptions.created_at')
            ->paginate(25);
        return view('admin.subscriptions.index', compact('subs'));
    }
}


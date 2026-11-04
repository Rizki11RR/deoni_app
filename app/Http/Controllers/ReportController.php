<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'orders' => Order::query()->with('customer')->latest()->limit(100)->get(),
            'salesTotal' => Order::query()->whereNot('status', 'cancelled')->sum('total'),
            'paymentTotal' => Payment::query()->sum('amount'),
            'outstandingTotal' => Order::query()->whereNot('status', 'cancelled')->get()->sum(fn (Order $order): float => (float) $order->total - (float) $order->payments()->sum('amount')),
        ]);
    }
}

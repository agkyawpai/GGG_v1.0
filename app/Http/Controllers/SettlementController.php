<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Settlement;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettlementController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(): Response
    {
        $settlements = Settlement::with(['order.shop', 'settledBy'])
            ->orderBy('settled_at', 'desc')
            ->get();

        return Inertia::render('Settlements/Index', [
            'settlements' => $settlements,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'cod_collected' => 'required|numeric|min:0',
            'total_cost'    => 'required|numeric|min:0',
        ]);

        $settlement = $this->service->recordSettlement($order, $validated);

        return back()->with('flash', [
            'type'    => 'success',
            'message' => "Settlement recorded for #{$order->ulid} — Net ₹{$settlement->net_amount}",
        ]);
    }
}

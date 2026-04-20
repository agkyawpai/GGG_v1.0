<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\Order;
use App\Models\Township;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogisticsController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(): Response
    {
        $orders = Order::with(['shop', 'customer', 'biker', 'township'])
            ->whereIn('status', ['pending', 'assigned', 'in_transit'])
            ->orderBy('ordered_at', 'desc')
            ->get();

        return Inertia::render('Logistics/Index', [
            'orders'    => $orders,
            'bikers'    => Biker::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'townships' => Township::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assign(Request $request, Order $order)
    {
        $validated = $request->validate([
            'biker_id'    => 'required|exists:bikers,id',
            'township_id' => 'required|exists:townships,id',
        ]);

        $this->service->assignBiker($order, $validated['biker_id'], $validated['township_id']);

        return back()->with('flash', ['type' => 'success', 'message' => 'Biker assigned']);
    }
}

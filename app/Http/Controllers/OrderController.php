<?php

namespace App\Http\Controllers;

use App\Events\OrderRegistered;
use App\Models\Biker;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Township;
use App\Models\Warehousing;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(): Response
    {
        $orders = Order::with(['shop', 'customer', 'biker', 'township'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        return Inertia::render('Dashboard', [
            'ordersByStatus' => $orders,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Orders/Create', [
            'shops'     => Shop::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone', 'township_id']),
            'townships' => Township::orderBy('name')->get(['id', 'name']),
            'bikers'    => Biker::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:type_a,type_b',
            'shop_id'     => 'required|exists:shops,id',
            'customer_id' => 'required|exists:customers,id',
            'township_id' => 'required|exists:townships,id',
            'item_name'   => 'required|string|max:255',
            'item_cost'   => 'required|numeric|min:0',
            'cod_amount'  => 'required|numeric|min:0',
            'delivery_fee'=> 'required|numeric|min:0',
            'notes'       => 'nullable|string',
            'shipped_via' => 'required_if:type,type_b|nullable|in:bus,express',
        ]);

        $order = Order::create([
            ...$validated,
            'ulid'       => strtolower((string) Str::ulid()),
            'ordered_at' => now(),
        ]);

        if ($order->type === 'type_b') {
            Warehousing::create([
                'order_id'    => $order->id,
                'status'      => 'in_transit',
                'shipped_via' => $validated['shipped_via'],
            ]);
        }

        broadcast(new OrderRegistered($order))->toOthers();

        return redirect()->route('dashboard')->with('flash', [
            'type'    => 'success',
            'message' => "Order #{$order->ulid} registered",
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['shop', 'customer', 'biker', 'township', 'warehousing.receivedBy', 'settlement.settledBy']);

        return Inertia::render('Orders/Show', [
            'order'              => $order,
            'allowedTransitions' => $this->service->allowedTransitions($order),
            'bikers'             => Biker::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
            'townships'          => Township::orderBy('name')->get(['id', 'name']),
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

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $this->service->transitionStatus($order, $validated['status']);

        return back()->with('flash', ['type' => 'success', 'message' => 'Order status updated']);
    }

    public function reject(Request $request, Order $order)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
            'return_fee'       => 'nullable|numeric|min:0',
        ]);

        $this->service->recordRejection($order, $validated['rejection_reason'], $validated['return_fee'] ?? null);

        return back()->with('flash', ['type' => 'error', 'message' => "Order #{$order->ulid} rejected"]);
    }
}

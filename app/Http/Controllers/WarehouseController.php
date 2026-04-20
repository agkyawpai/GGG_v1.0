<?php

namespace App\Http\Controllers;

use App\Events\ItemReceived;
use App\Models\Warehousing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(): Response
    {
        $items = Warehousing::with(['order.shop', 'receivedBy'])
            ->where('status', 'stored')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Warehouse/Index', [
            'items' => $items,
        ]);
    }

    public function receive(Request $request, Warehousing $warehousing)
    {
        $warehousing->update([
            'status'      => 'received',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Item marked as received']);
    }

    public function store(Request $request, Warehousing $warehousing)
    {
        $warehousing->update(['status' => 'stored']);

        broadcast(new ItemReceived($warehousing))->toOthers();

        return back()->with('flash', ['type' => 'success', 'message' => 'Item stored in warehouse']);
    }

    public function dispatch(Request $request, Warehousing $warehousing)
    {
        $warehousing->update([
            'status'        => 'dispatched',
            'dispatched_at' => now(),
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Item dispatched from warehouse']);
    }
}

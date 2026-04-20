<?php

namespace App\Services;

use App\Events\BikerAssigned;
use App\Events\OrderStatusChanged;
use App\Events\SettlementRecorded;
use App\Models\Order;
use App\Models\Settlement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const TRANSITIONS = [
        'pending'    => ['assigned'],
        'assigned'   => ['in_transit'],
        'in_transit' => ['delivered'],
        'delivered'  => ['settled', 'returned'],
        'returned'   => ['settled'],
        'settled'    => [],
    ];

    public function transitionStatus(Order $order, string $newStatus): Order
    {
        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$order->status}' to '{$newStatus}'.",
            ]);
        }

        $oldStatus = $order->status;
        $order->status = $newStatus;

        if ($newStatus === 'delivered') {
            $order->delivered_at = now();
        }
        if ($newStatus === 'settled') {
            $order->settled_at = now();
        }

        $order->save();

        broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus))->toOthers();

        return $order;
    }

    public function assignBiker(Order $order, int $bikerId, int $townshipId): Order
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'biker_id' => 'Can only assign biker to pending orders.',
            ]);
        }

        $order->biker_id    = $bikerId;
        $order->township_id = $townshipId;
        $order->status      = 'assigned';
        $order->save();

        broadcast(new BikerAssigned($order))->toOthers();

        return $order;
    }

    public function recordSettlement(Order $order, array $data): Settlement
    {
        if (! in_array($order->status, ['delivered', 'returned'])) {
            throw ValidationException::withMessages([
                'order' => 'Order must be delivered or returned to settle.',
            ]);
        }

        $netAmount = $data['cod_collected'] - $data['total_cost'];

        $settlement = Settlement::create([
            'order_id'      => $order->id,
            'total_cost'    => $data['total_cost'],
            'delivery_fee'  => $order->delivery_fee,
            'cod_collected' => $data['cod_collected'],
            'net_amount'    => $netAmount,
            'settled_by'    => Auth::id(),
            'settled_at'    => now(),
        ]);

        $this->transitionStatus($order, 'settled');

        broadcast(new SettlementRecorded($settlement))->toOthers();

        return $settlement;
    }

    public function recordRejection(Order $order, string $reason, ?float $returnFee = null): Order
    {
        $order->rejection_reason = $reason;
        $order->return_fee       = $returnFee;
        $order->save();

        return $this->transitionStatus($order, 'returned');
    }

    public function allowedTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }
}

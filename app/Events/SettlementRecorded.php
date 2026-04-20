<?php

namespace App\Events;

use App\Models\Settlement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettlementRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Settlement $settlement) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders'),
            new PrivateChannel("order.{$this->settlement->order_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'settlement' => $this->settlement->load('order')->toArray(),
        ];
    }
}

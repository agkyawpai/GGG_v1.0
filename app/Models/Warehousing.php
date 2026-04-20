<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehousing extends Model
{
    use HasFactory;

    protected $table = 'warehousing';

    protected $fillable = [
        'order_id', 'status', 'shipped_via',
        'received_by', 'received_at', 'dispatched_at',
    ];

    protected $casts = [
        'received_at'   => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'total_cost', 'delivery_fee',
        'cod_collected', 'net_amount', 'settled_by', 'settled_at',
    ];

    protected $casts = [
        'total_cost'    => 'decimal:2',
        'delivery_fee'  => 'decimal:2',
        'cod_collected' => 'decimal:2',
        'net_amount'    => 'decimal:2',
        'settled_at'    => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}

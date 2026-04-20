<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Order extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'ulid', 'type', 'status',
        'shop_id', 'customer_id', 'biker_id', 'township_id',
        'item_name', 'item_cost', 'cod_amount', 'delivery_fee',
        'rejection_reason', 'return_fee', 'notes',
        'ordered_at', 'delivered_at', 'settled_at',
    ];

    protected $casts = [
        'item_cost'    => 'decimal:2',
        'cod_amount'   => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'return_fee'   => 'decimal:2',
        'ordered_at'   => 'datetime',
        'delivered_at' => 'datetime',
        'settled_at'   => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function biker()
    {
        return $this->belongsTo(Biker::class);
    }

    public function township()
    {
        return $this->belongsTo(Township::class);
    }

    public function warehousing()
    {
        return $this->hasOne(Warehousing::class);
    }

    public function settlement()
    {
        return $this->hasOne(Settlement::class);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}

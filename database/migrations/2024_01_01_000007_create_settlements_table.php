<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_cost', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('cod_collected', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->foreignId('settled_by')->constrained('users');
            $table->timestamp('settled_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};

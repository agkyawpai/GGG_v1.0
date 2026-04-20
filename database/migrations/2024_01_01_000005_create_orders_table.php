<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->enum('type', ['type_a', 'type_b']);
            $table->enum('status', ['pending', 'assigned', 'in_transit', 'delivered', 'returned', 'settled'])->default('pending');
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('biker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('township_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('item_cost', 10, 2);
            $table->decimal('cod_amount', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->text('rejection_reason')->nullable();
            $table->decimal('return_fee', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

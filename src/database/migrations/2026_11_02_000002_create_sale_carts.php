<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('sale_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_cart_id')->constrained('sale_carts')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->unsignedBigInteger('price_cents');
            $table->unique(['sale_cart_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_cart_items');
        Schema::dropIfExists('sale_carts');
    }
};

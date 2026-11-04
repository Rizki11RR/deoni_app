<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_packagings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uom_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->decimal('quantity', 15, 6)->default(1);
            $table->decimal('store_price', 15, 2)->nullable();
            $table->decimal('hotel_price', 15, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'uom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_packagings');
    }
};

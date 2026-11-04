<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uoms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uom_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('symbol', 20);
            $table->decimal('conversion_factor', 15, 6)->default(1);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['uom_category_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uoms');
    }
};

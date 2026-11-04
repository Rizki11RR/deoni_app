<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('master_unit_price', 15, 2)->nullable()->after('unit_price');
            $table->decimal('price_difference', 15, 2)->default(0)->after('master_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['master_unit_price', 'price_difference']);
        });
    }
};

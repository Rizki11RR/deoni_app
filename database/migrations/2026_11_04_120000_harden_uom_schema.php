<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uom_categories', function (Blueprint $table): void {
            $table->softDeletes();
        });
        Schema::table('uoms', function (Blueprint $table): void {
            $table->string('conversion_type', 20)->default('multiply')->after('conversion_factor');
            $table->unsignedTinyInteger('precision')->default(3)->after('conversion_type');
            $table->softDeletes();
        });
        Schema::table('product_packagings', function (Blueprint $table): void {
            $table->softDeletes();
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('purchase_uom_id')->nullable()->after('uom_id')->constrained('uoms')->nullOnDelete();
            $table->foreignId('sales_uom_id')->nullable()->after('purchase_uom_id')->constrained('uoms')->nullOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('uom_id')->nullable()->after('product_id')->constrained('uoms')->nullOnDelete();
            $table->decimal('base_quantity', 15, 6)->nullable()->after('quantity');
            $table->string('uom_symbol', 20)->nullable()->after('unit');
            $table->decimal('uom_factor', 15, 6)->nullable()->after('uom_symbol');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('uom_id'));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn(['base_quantity', 'uom_symbol', 'uom_factor']));
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('purchase_uom_id');
            $table->dropConstrainedForeignId('sales_uom_id');
        });
        Schema::table('product_packagings', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('uoms', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn(['conversion_type', 'precision']);
        });
        Schema::table('uom_categories', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};

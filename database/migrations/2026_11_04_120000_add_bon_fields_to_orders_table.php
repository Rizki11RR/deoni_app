<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->date('due_date')->nullable()->after('total');
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('due_date');
            $table->index(['document_type', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['document_type', 'due_date']);
            $table->dropColumn(['due_date', 'approved_by', 'approved_at']);
        });
    }
};

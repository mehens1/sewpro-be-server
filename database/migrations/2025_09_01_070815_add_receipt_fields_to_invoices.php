<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('due_date');
            $table->timestamp('receipt_at')->nullable()->after('receipt_number');
            $table->timestamp('cancelled_at')->nullable()->after('receipt_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'receipt_at', 'cancelled_at']);
        });
    }
};

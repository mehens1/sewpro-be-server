<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_type');
            $table->boolean('is_staff')->default(false)->after('password');
            $table->boolean('is_admin')->default(false)->after('is_staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type');
            $table->dropColumn(['is_staff', 'is_admin']);
        });
    }
};

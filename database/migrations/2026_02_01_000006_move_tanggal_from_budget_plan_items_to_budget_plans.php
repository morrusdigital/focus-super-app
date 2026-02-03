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
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('notes');
        });

        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('vendor_name');
        });

        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};

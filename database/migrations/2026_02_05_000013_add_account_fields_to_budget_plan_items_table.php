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
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('project_id')
                ->constrained('company_bank_accounts')
                ->nullOnDelete();
            $table->foreignId('chart_account_id')
                ->nullable()
                ->after('bank_account_id')
                ->constrained('chart_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropConstrainedForeignId('chart_account_id');
        });
    }
};

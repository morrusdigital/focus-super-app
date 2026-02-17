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
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->foreignId('budget_plan_id')
                ->nullable()
                ->after('project_id')
                ->constrained('budget_plans')
                ->nullOnDelete();

            $table->foreignId('budget_plan_item_id')
                ->nullable()
                ->after('budget_plan_id')
                ->constrained('budget_plan_items')
                ->nullOnDelete();

            $table->string('expense_source', 30)
                ->default('manual_project')
                ->after('budget_plan_item_id');

            $table->index('budget_plan_id');
            $table->index('budget_plan_item_id');
            $table->index(['expense_source', 'budget_plan_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropIndex(['expense_source', 'budget_plan_item_id']);
            $table->dropIndex(['budget_plan_item_id']);
            $table->dropIndex(['budget_plan_id']);
            $table->dropConstrainedForeignId('budget_plan_item_id');
            $table->dropConstrainedForeignId('budget_plan_id');
            $table->dropColumn('expense_source');
        });
    }
};

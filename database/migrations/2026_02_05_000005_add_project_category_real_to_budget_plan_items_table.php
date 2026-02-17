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
            $table->foreignId('project_id')
                ->nullable()
                ->after('budget_plan_id')
                ->constrained('projects')
                ->nullOnDelete();
            $table->string('category')->nullable()->after('vendor_name');
            $table->decimal('real_amount', 18, 2)->default(0)->after('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn('category');
            $table->dropColumn('real_amount');
        });
    }
};

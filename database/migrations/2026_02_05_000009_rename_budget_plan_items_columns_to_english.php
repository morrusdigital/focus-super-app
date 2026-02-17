<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->decimal('unit_price', 18, 2)->default(0)->after('category');
            $table->decimal('quantity', 18, 2)->default(0)->after('unit_price');
            $table->string('unit')->default('unit')->after('quantity');
            $table->decimal('line_total', 18, 2)->default(0)->after('unit');
        });

        DB::table('budget_plan_items')->update([
            'unit_price' => DB::raw('harsat'),
            'quantity' => DB::raw('qty'),
            'unit' => DB::raw('satuan'),
            'line_total' => DB::raw('jumlah'),
        ]);

        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->dropColumn(['harsat', 'qty', 'satuan', 'jumlah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->decimal('harsat', 18, 2)->default(0)->after('category');
            $table->decimal('qty', 18, 2)->default(0)->after('harsat');
            $table->string('satuan')->default('unit')->after('qty');
            $table->decimal('jumlah', 18, 2)->default(0)->after('satuan');
        });

        DB::table('budget_plan_items')->update([
            'harsat' => DB::raw('unit_price'),
            'qty' => DB::raw('quantity'),
            'satuan' => DB::raw('unit'),
            'jumlah' => DB::raw('line_total'),
        ]);

        Schema::table('budget_plan_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'quantity', 'unit', 'line_total']);
        });
    }
};

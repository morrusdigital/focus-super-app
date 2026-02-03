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
        Schema::create('budget_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('kode');
            $table->string('vendor_name')->nullable();
            $table->date('tanggal');
            $table->decimal('harsat', 18, 2);
            $table->decimal('qty', 18, 2);
            $table->string('satuan');
            $table->decimal('jumlah', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_plan_items');
    }
};

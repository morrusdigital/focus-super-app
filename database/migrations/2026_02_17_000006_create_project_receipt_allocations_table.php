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
        Schema::create('project_receipt_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_receipt_id')->constrained('project_receipts')->cascadeOnDelete();
            $table->foreignId('project_term_id')->constrained('project_terms')->cascadeOnDelete();
            $table->decimal('amount', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_receipt_allocations');
    }
};

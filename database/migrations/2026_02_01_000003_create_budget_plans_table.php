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
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->string('bp_number')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'revision_requested'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('revision_requested_at')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_plans');
    }
};

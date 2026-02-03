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
        Schema::create('budget_plan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_plan_logs');
    }
};

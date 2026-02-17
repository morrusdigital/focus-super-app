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
        Schema::create('project_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('sequence_no');
            $table->string('name');
            $table->string('basis_type', 20);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 18, 2);
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'sequence_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_terms');
    }
};

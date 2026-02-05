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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('position')->default(0);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->json('labels')->nullable();
            $table->timestamps();

            $table->index(['column_id', 'position']);
            $table->index('assignee_id');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};

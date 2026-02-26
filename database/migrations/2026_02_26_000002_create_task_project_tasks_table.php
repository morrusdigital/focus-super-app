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
        Schema::create('task_project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_project_id')->constrained('task_projects')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['todo', 'doing', 'blocked', 'done'])->default('todo');
            $table->integer('progress')->default(0);
            $table->text('blocked_reason')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['task_project_id', 'status']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_project_tasks');
    }
};

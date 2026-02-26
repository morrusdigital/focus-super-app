<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            // Invoice proof attachment
            $table->string('invoice_proof_path')->nullable()->after('notes');
            $table->string('invoice_proof_original_name')->nullable()->after('invoice_proof_path');
            $table->string('invoice_proof_mime', 100)->nullable()->after('invoice_proof_original_name');
            $table->unsignedBigInteger('invoice_proof_size')->nullable()->after('invoice_proof_mime');
            $table->foreignId('invoice_proof_uploaded_by')->nullable()->after('invoice_proof_size')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('invoice_proof_uploaded_at')->nullable()->after('invoice_proof_uploaded_by');

            // Bank mutation attachment
            $table->string('bank_mutation_path')->nullable()->after('invoice_proof_uploaded_at');
            $table->string('bank_mutation_original_name')->nullable()->after('bank_mutation_path');
            $table->string('bank_mutation_mime', 100)->nullable()->after('bank_mutation_original_name');
            $table->unsignedBigInteger('bank_mutation_size')->nullable()->after('bank_mutation_mime');
            $table->foreignId('bank_mutation_uploaded_by')->nullable()->after('bank_mutation_size')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('bank_mutation_uploaded_at')->nullable()->after('bank_mutation_uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_proof_uploaded_by');
            $table->dropConstrainedForeignId('bank_mutation_uploaded_by');
            $table->dropColumn([
                'invoice_proof_path',
                'invoice_proof_original_name',
                'invoice_proof_mime',
                'invoice_proof_size',
                'invoice_proof_uploaded_at',
                'bank_mutation_path',
                'bank_mutation_original_name',
                'bank_mutation_mime',
                'bank_mutation_size',
                'bank_mutation_uploaded_at',
            ]);
        });
    }
};

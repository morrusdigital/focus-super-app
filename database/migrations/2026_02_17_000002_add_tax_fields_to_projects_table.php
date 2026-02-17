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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('address')->nullable()->after('name');
            $table->decimal('contract_value', 18, 2)->nullable()->after('address');

            $table->boolean('use_pph')->nullable()->after('contract_value');
            $table->foreignId('pph_tax_master_id')
                ->nullable()
                ->after('use_pph')
                ->constrained('tax_masters')
                ->nullOnDelete();
            $table->decimal('pph_rate', 5, 2)->nullable()->after('pph_tax_master_id');

            $table->boolean('use_ppn')->nullable()->after('pph_rate');
            $table->foreignId('ppn_tax_master_id')
                ->nullable()
                ->after('use_ppn')
                ->constrained('tax_masters')
                ->nullOnDelete();
            $table->decimal('ppn_rate', 5, 2)->nullable()->after('ppn_tax_master_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pph_tax_master_id');
            $table->dropConstrainedForeignId('ppn_tax_master_id');
            $table->dropColumn([
                'address',
                'contract_value',
                'use_pph',
                'pph_rate',
                'use_ppn',
                'ppn_rate',
            ]);
        });
    }
};

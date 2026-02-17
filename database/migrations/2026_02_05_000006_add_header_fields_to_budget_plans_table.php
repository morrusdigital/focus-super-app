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
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->date('tanggal_pengajuan')->nullable()->after('tanggal');
            $table->unsignedTinyInteger('minggu_ke')->nullable()->after('tanggal_pengajuan');
            $table->unsignedInteger('jumlah_project')->nullable()->after('minggu_ke');
            $table->string('kategori')->nullable()->after('jumlah_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pengajuan', 'minggu_ke', 'jumlah_project', 'kategori']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->date('bp_date')->nullable()->after('tanggal');
            $table->date('submission_date')->nullable()->after('bp_date');
            $table->unsignedTinyInteger('week_of_month')->nullable()->after('submission_date');
            $table->unsignedInteger('project_count')->nullable()->after('week_of_month');
            $table->string('category')->nullable()->after('project_count');
        });

        DB::table('budget_plans')->update([
            'bp_date' => DB::raw('tanggal'),
            'submission_date' => DB::raw('tanggal_pengajuan'),
            'week_of_month' => DB::raw('minggu_ke'),
            'project_count' => DB::raw('jumlah_project'),
            'category' => DB::raw('kategori'),
        ]);

        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn(['tanggal', 'tanggal_pengajuan', 'minggu_ke', 'jumlah_project', 'kategori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('notes');
            $table->date('tanggal_pengajuan')->nullable()->after('tanggal');
            $table->unsignedTinyInteger('minggu_ke')->nullable()->after('tanggal_pengajuan');
            $table->unsignedInteger('jumlah_project')->nullable()->after('minggu_ke');
            $table->string('kategori')->nullable()->after('jumlah_project');
        });

        DB::table('budget_plans')->update([
            'tanggal' => DB::raw('bp_date'),
            'tanggal_pengajuan' => DB::raw('submission_date'),
            'minggu_ke' => DB::raw('week_of_month'),
            'jumlah_project' => DB::raw('project_count'),
            'kategori' => DB::raw('category'),
        ]);

        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn(['bp_date', 'submission_date', 'week_of_month', 'project_count', 'category']);
        });
    }
};

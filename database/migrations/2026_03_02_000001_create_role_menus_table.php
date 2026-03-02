<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_menus', function (Blueprint $table) {
            $table->id();
            // One row per role slug, e.g. 'company_admin', 'finance_holding', etc.
            $table->string('role', 50)->unique();
            // JSON array of enabled menu keys, e.g. ["projects","my_tasks"]
            $table->json('menu_keys');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menus');
    }
};

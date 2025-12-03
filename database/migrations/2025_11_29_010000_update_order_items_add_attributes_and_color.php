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
        Schema::table('order_items', function (Blueprint $table) {
            // تخزين الخصائص المختارة كـ JSON
            $table->json('attributes')->nullable()->after('total');
            // اللون المختار (إن وجد)
            $table->string('color')->nullable()->after('attributes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['attributes', 'color']);
        });
    }
};



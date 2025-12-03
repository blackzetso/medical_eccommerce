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
        Schema::table('cart', function (Blueprint $table) {
            // سعر الوحدة بعد إضافة الخصائص واللون
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            // السعر الإجمالي (سعر الوحدة * الكمية)
            $table->decimal('total_price', 10, 2)->nullable()->after('unit_price');
            // تخزين الخصائص المختارة كـ JSON
            $table->json('attributes')->nullable()->after('total_price');
            // اللون المختار (إن وجد)
            $table->string('color')->nullable()->after('attributes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_price', 'attributes', 'color']);
        });
    }
};



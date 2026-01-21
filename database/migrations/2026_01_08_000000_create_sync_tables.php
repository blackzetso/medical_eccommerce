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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->json('attributes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('variant_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('SAR');
            $table->boolean('tax_included')->default(true);
            $table->timestamps();
        });

        Schema::create('variant_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('available')->default(0);
            $table->integer('reserved')->default(0);
            $table->timestamps();
        });

        Schema::create('external_references', function (Blueprint $table) {
            $table->id();
            $table->string('local_type'); // product | variant
            $table->unsignedBigInteger('local_id');
            $table->string('remote_id');
            $table->string('source_system'); // store | erp
            $table->timestamps();

            $table->unique(['local_type', 'local_id', 'source_system'], 'external_refs_local_unique');
            $table->unique(['local_type', 'remote_id', 'source_system'], 'external_refs_remote_unique');
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('method');
            $table->string('path');
            $table->string('body_hash');
            $table->json('response')->nullable();
            $table->timestamps();
            $table->index(['method', 'path']);
        });

        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();
            $table->string('event_type');
            $table->string('source');
            $table->string('status')->default('pending'); // pending|processed|ignored|failed
            $table->unsignedInteger('attempts')->default(0);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->string('target_system')->default('erp');
            $table->string('source')->default('store');
            $table->string('status')->default('pending'); // pending|sent|failed
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('event_logs');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('external_references');
        Schema::dropIfExists('variant_stocks');
        Schema::dropIfExists('variant_prices');
        Schema::dropIfExists('product_variants');
    }
};

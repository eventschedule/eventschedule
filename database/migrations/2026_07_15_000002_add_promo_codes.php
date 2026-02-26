<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 13, 3);
            $table->integer('max_uses')->nullable();
            $table->integer('times_used')->default(0);
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('ticket_ids')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'code']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('boost_campaign_id')
                ->constrained('promo_codes')->nullOnDelete();
            $table->decimal('discount_amount', 13, 3)->nullable()->after('promo_code_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
            $table->dropColumn(['promo_code_id', 'discount_amount']);
        });

        Schema::dropIfExists('promo_codes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->string('secret', 32);
            $table->decimal('amount', 13, 3);
            $table->decimal('remaining_amount', 13, 3);
            $table->string('currency_code', 3);
            $table->enum('status', ['unpaid', 'active', 'cancelled', 'refunded', 'amount_mismatch'])->default('unpaid');
            $table->string('payment_method', 20)->default('cash');
            $table->string('transaction_reference')->nullable();
            $table->string('purchaser_name');
            $table->string('purchaser_email');
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->text('message')->nullable();
            $table->integer('valid_days')->nullable();
            $table->datetime('activated_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['role_id', 'status']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('gift_card_id')->nullable()->after('discount_amount')
                ->constrained('gift_cards')->nullOnDelete();
            $table->decimal('gift_card_amount', 13, 3)->nullable()->after('gift_card_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('gift_cards_enabled')->default(false);
            $table->json('gift_card_amounts')->nullable();
            $table->string('gift_card_currency_code', 3)->nullable();
            $table->integer('gift_card_valid_days')->nullable();
            $table->string('gift_card_payment_method', 20)->default('cash');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'gift_cards_enabled',
                'gift_card_amounts',
                'gift_card_currency_code',
                'gift_card_valid_days',
                'gift_card_payment_method',
            ]);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['gift_card_id']);
            $table->dropColumn(['gift_card_id', 'gift_card_amount']);
        });

        Schema::dropIfExists('gift_cards');
    }
};

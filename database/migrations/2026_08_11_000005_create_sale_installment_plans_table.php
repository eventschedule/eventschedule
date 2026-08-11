<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One plan per paying sale. The sale itself flips to `paid` on the first installment - which
     * is what makes the ticket valid immediately and keeps ReleaseTickets from expiring the seat -
     * so the outstanding balance has to live somewhere else. This is that somewhere.
     */
    public function up(): void
    {
        Schema::create('sale_installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->unique()->constrained()->cascadeOnDelete();

            // Snapshot, never read off the event: an owner can change ticket_currency_code while
            // a plan is mid-flight, and every amount here was agreed in the original currency.
            $table->string('currency', 3);
            $table->decimal('total_amount', 13, 3);
            $table->decimal('amount_paid', 13, 3)->default(0);
            $table->unsignedTinyInteger('installment_count');
            $table->enum('status', ['active', 'completed', 'delinquent', 'cancelled'])->default('active');

            // The Customer and PaymentMethod live on the event owner's CONNECTED account, not the
            // platform account, so the account id has to be stored beside them - unlinking and
            // relinking Stripe issues a new account and invalidates both.
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();

            // Free on the PaymentIntent's PaymentMethod, and they pay for themselves: emails can
            // say "your Visa ending 4242" rather than "your card", and a card expiring before the
            // final due date is a guaranteed future failure we can warn about in advance.
            $table->string('card_brand', 20)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->unsignedTinyInteger('card_exp_month')->nullable();
            $table->unsignedSmallInteger('card_exp_year')->nullable();

            // The consent checkbox on our own checkout page, not Stripe's disclosure. This is the
            // only artefact we hold if a buyer later disputes the mandate.
            $table->timestamp('mandate_accepted_at')->nullable();
            $table->string('mandate_ip', 45)->nullable();

            $table->timestamp('delinquent_at')->nullable();
            $table->string('secret', 32);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_installment_plans');
    }
};

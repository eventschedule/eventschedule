<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_installment_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->decimal('amount', 13, 3);
            $table->dateTime('due_at');

            // `processing` is a real claim, not decoration: this is the only place in the app
            // where WE initiate a charge, so the Stripe call is the mutation and it happens
            // before any local write. The cron claims the row (scheduled -> processing) inside a
            // transaction and only then talks to Stripe.
            //
            // `awaiting_customer` is the SCA park. An `authentication_required` decline will fail
            // identically on every retry, so it must not sit in the backoff ladder burning
            // attempts towards a delinquency the buyer cannot prevent.
            $table->enum('status', [
                'scheduled',
                'processing',
                'awaiting_customer',
                'paid',
                'failed',
                'cancelled',
            ])->default('scheduled');

            $table->timestamp('paid_at')->nullable();

            // Per-installment, because the sale's single transaction_reference cannot identify N
            // charges. The organizer refunds by hand on their own Stripe dashboard (nothing in
            // this app refunds a Connect ticket sale), so these are what the Installments tab
            // shows them.
            $table->string('transaction_reference')->nullable();

            $table->unsignedTinyInteger('attempts')->default(0);

            // Without this the selection query is just "scheduled and due", and the command runs
            // hourly - so a declined card would be retried 24 times a day, forever. That is a
            // card-testing pattern and gets the organizer's Stripe account rate-limited.
            $table->dateTime('next_attempt_at')->nullable();

            $table->string('last_error')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('failed_notice_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['sale_installment_plan_id', 'sequence']);
            $table->index(['status', 'due_at', 'next_attempt_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_installments');
    }
};

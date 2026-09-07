<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();

            // Set only for an installment leg. A plan is N separate charges, each with its own
            // PaymentIntent on sale_installments.transaction_reference, and the sale's single
            // transaction_reference cannot identify them - so a refund of a plan is N rows here.
            $table->foreignId('sale_installment_id')->nullable()->constrained()->nullOnDelete();

            // Matches sales.payment_amount's precision. NOT the sale's own payment_amount, which
            // on a group or order primary is the per-seat figure: the ceiling is
            // isOrderPrimary() ? orderTotalPayment() : legTotalPayment(), the same expression
            // SaleSettlementService reconciles the charge against.
            $table->decimal('amount', 13, 3);

            // sales has no currency column; it comes from events.ticket_currency_code. Snapshotted
            // here because a refund is a historical fact and the event's currency can be edited.
            $table->string('currency_code', 3)->nullable();

            $table->string('gateway', 32);
            $table->string('gateway_refund_id')->nullable();

            // `pending` is a real claim, not decoration. The row is inserted inside a small
            // transaction while the sale is locked, and only then does the gateway call happen -
            // outside the lock, because holding a row lock across a Stripe round trip is what
            // SaleSettlementService's own comment forbids.
            //
            // `awaiting_reconciliation` is the unknown-outcome park, mirroring sale_installments.
            // A timeout may well have moved the money, and Stripe idempotency keys expire after
            // 24h, so a later retry with a fresh key is exactly how one refund becomes two. These
            // are resolved by a person against the dashboard, never by re-issuing the call.
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'awaiting_reconciliation',
            ])->default('pending');

            // The claim itself. Unique so a double-clicked Refund cannot produce two gateway
            // calls, and passed to the gateway so even a retried HTTP request settles once.
            $table->string('idempotency_key')->unique();

            // Who pressed the button. Nullable because a refund can originate from the API key
            // path or a site admin acting on someone else's sale.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reason')->nullable();
            $table->string('error_code')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();

            // The ceiling query: every row for a sale that is not `failed` holds its amount
            // against the remaining refundable balance.
            $table->index(['sale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_refunds');
    }
};

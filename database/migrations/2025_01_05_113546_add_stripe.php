<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('plan_term', ['month', 'year'])->default('year');
            $table->enum('plan_type', ['free', 'pro', 'enterprise'])->default('free');
        });

        DB::table('roles')->update([
            'plan_expires' => now()->addYear()->format('Y-m-d'),
            'plan_type' => 'pro',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_company_name')->nullable();
            $table->timestamp('stripe_completed_at')->nullable();
            $table->string('invoiceninja_api_key')->nullable();
            $table->string('invoiceninja_api_url')->nullable();
            $table->string('invoiceninja_company_name')->nullable();
            $table->string('invoiceninja_webhook_secret')->nullable();
        });


        Schema::table('events', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'stripe', 'invoiceninja'])->default('cash');
            $table->text('payment_instructions')->nullable();
            $table->integer('expire_unpaid_tickets')->default(0);
            $table->string('ticket_currency_code')->default('USD')->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('sold');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->text('sold')->default('{}');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropColumn(['ticket_id', 'quantity', 'is_used', 'is_paid']);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded', 'expired'])->default('pending');
            $table->string('event_date');
            $table->enum('payment_method', ['cash', 'stripe', 'invoiceninja'])->default('cash');
        });

        Schema::create('sale_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->text('seats');
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['plan_term', 'plan_type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_company_name',
                'stripe_completed_at',
                'invoiceninja_api_key',
                'invoiceninja_api_url',
                'invoiceninja_company_name',
                'invoiceninja_webhook_secret',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_instructions', 'expire_unpaid_tickets']);
        });

        Schema::dropIfExists('sale_tickets');

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->nullable();
            $table->boolean('is_used')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->dropColumn(['event_date', 'status', 'payment_method']);
        });
    }
};

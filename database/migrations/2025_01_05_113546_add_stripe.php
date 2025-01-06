<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable();
            $table->timestamp('stripe_completed_at')->nullable();
            $table->string('invoiceninja_api_key')->nullable();
        });


        Schema::table('events', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'stripe', 'invoiceninja'])->default('cash');
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
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->string('event_date');
        });

        Schema::create('sale_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('quantity_used')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_completed_at',
                'invoiceninja_api_key',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::dropIfExists('sale_tickets');

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->nullable();
            $table->boolean('is_used')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->dropColumn(['event_date', 'status']);
        });
    }
};

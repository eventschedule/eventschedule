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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('header_image')->nullable();
            $table->text('default_tickets')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('tickets_enabled')->default(false);            
            $table->string('ticket_currency_code')->nullable();
            $table->text('ticket_notes')->nullable();
        });

        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('sold')->default(0);
            $table->decimal('price', 13, 3)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('secret');
            $table->integer('quantity');
            $table->boolean('is_used')->default(false);
            $table->string('transaction_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('header_image');
            $table->dropColumn('default_tickets');
        });        

        Schema::dropIfExists('event_tickets');
        Schema::dropIfExists('tickets');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tickets_enabled');
            $table->dropColumn('ticket_currency_code');
            $table->dropColumn('ticket_notes');
        });        
    }
};

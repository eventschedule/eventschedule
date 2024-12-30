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
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('tickets_enabled')->default(false);            
            $table->integer('ticket_quantity')->nullable();
            $table->integer('ticket_sold')->nullable();
            $table->decimal('ticket_price', 13, 3)->nullable();
            $table->string('ticket_types')->nullable();
            $table->string('ticket_currency_code')->nullable();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->integer('quantity');
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
        });        

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tickets_enabled');
            $table->dropColumn('ticket_quantity');
            $table->dropColumn('ticket_sold');
            $table->dropColumn('ticket_price');
            $table->dropColumn('ticket_types');
            $table->dropColumn('ticket_currency_code');
        });

        Schema::dropIfExists('tickets');
    }
};

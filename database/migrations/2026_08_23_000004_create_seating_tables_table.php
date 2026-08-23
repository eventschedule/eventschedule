<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A round or rectangular dining table inside a kind=table section.
     *
     * A table is NOT a section: a room of twenty tables is one pricing band, so making
     * each table a section would mean twenty bands. Seats hang off the table, and
     * booking_mode decides whether a guest may take one chair or must take the lot.
     */
    public function up(): void
    {
        Schema::create('seating_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_section_id')->constrained()->cascadeOnDelete();

            $table->string('label', 50);
            $table->string('shape', 10)->default('round'); // round|rect
            $table->unsignedSmallInteger('seat_count')->default(0);
            $table->string('booking_mode', 10)->default('seat'); // seat|whole|either

            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->smallInteger('rotation')->default(0);
            $table->unsignedSmallInteger('width')->default(120);
            $table->unsignedSmallInteger('height')->default(120);
            $table->timestamps();

            $table->index(['seating_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_tables');
    }
};

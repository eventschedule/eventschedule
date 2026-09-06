<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Addresses on a schedule that matched nothing, counted per day.
     *
     * A dead event address used to 302 to the schedule home, so the visit was booked against the
     * schedule and against no event: the schedule's view total went up, the event the address named
     * stayed at zero, and nobody could tell the difference between "this link is broken" and "these
     * analytics are broken". The address now 404s, and this is where those 404s are counted so an
     * owner can find a rotted link instead of inferring one.
     *
     * slug is 191 to stay inside the 3072-byte index limit on utf8mb4 alongside role_id and date.
     */
    public function up(): void
    {
        Schema::create('analytics_missing_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('slug', 191);
            $table->unsignedInteger('views')->default(0);

            $table->unique(['role_id', 'date', 'slug'], 'missing_daily_unique');
            $table->index(['role_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_missing_daily');
    }
};

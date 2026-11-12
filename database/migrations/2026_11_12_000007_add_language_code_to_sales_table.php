<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The language the guest was reading the event in when they bought or registered: the
     * schedule's own language or its translation language, as the event page showed it.
     *
     * Their confirmation, reminder, feedback request, change and cancellation notices, calendar
     * invite and wallet pass follow it (Sale::guestLanguage()). Null for every sale made before
     * this, and for imports and the box office, where nobody was reading a page: those mails keep
     * the language they always had.
     *
     * Short and nullable, and on sales, which is nowhere near the row limit that roles is at.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('language_code', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('language_code');
        });
    }
};

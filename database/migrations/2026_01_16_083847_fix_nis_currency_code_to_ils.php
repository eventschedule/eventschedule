<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update any events with invalid "NIS" currency code to valid "ILS" (ISO 4217)
     */
    public function up(): void
    {
        DB::table('events')
            ->where('ticket_currency_code', 'NIS')
            ->update(['ticket_currency_code' => 'ILS']);
    }

    /**
     * Reverse the migrations.
     * Revert "ILS" back to "NIS" (though this is not recommended)
     */
    public function down(): void
    {
        DB::table('events')
            ->where('ticket_currency_code', 'ILS')
            ->update(['ticket_currency_code' => 'NIS']);
    }
};

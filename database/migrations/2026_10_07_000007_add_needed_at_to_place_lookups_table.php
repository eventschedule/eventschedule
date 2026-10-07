<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a map last read an address (App\Services\PlaceLookupService::stillNeeded()).
 *
 * place_lookups is keyed by the address and linked to nothing, so nothing ever removed a row: a
 * venue's street address and coordinates outlived the venue, its schedule and its owner's
 * account. A venue's address is often somebody's home. With this date the runner deletes a row no
 * map has read for KEEP_DAYS, and it is set at most once a day per row, not on every page view.
 *
 * Nullable: rows written before it existed are dated by their created_at until a map reads them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('place_lookups', function (Blueprint $table) {
            $table->date('needed_at')->nullable();
            $table->index('needed_at');
        });
    }

    public function down(): void
    {
        Schema::table('place_lookups', function (Blueprint $table) {
            $table->dropIndex(['needed_at']);
            $table->dropColumn('needed_at');
        });
    }
};

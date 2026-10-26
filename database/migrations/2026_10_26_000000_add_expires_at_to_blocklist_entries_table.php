<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a list entry stops refusing new accounts (/admin/blocked): a country turned away for a
 * week while one person keeps making spam accounts from it, or an address for a day. Null is
 * until removed, which is what every entry made before this column was.
 *
 * An entry that has ended is kept, so the list still shows it and how often it refused, until
 * the operator removes it or adds it again (App\Services\Blocklist::add(), which moves the end).
 * No new index: every lookup goes through the unique (type, match_key) first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocklist_entries', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blocklist_entries', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};

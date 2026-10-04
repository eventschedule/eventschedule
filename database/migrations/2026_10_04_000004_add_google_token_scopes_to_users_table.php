<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a Google Calendar connection was granted. The import page asks for read access only;
     * sending events to Google needs more, and the app has to know which it holds. NULL is a
     * connection made before this column existed, when every connection asked for the full set.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'google_token_scopes')) {
            return;
        }

        // Short, as in this day's other migrations: a busy users table makes the deploy fail
        // and retry rather than queue every sign-in behind the ALTER.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->text('google_token_scopes')->nullable();
            });
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'google_token_scopes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('google_token_scopes');
            });
        }
    }
};

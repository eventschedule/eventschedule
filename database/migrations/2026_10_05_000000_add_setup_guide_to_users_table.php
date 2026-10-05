<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a new organizer is in the setup guide (App\Utils\SetupGuide): which schedule it
     * belongs to, when it started, and what they hid, answered or finished. NULL is everybody
     * who never saved a first schedule through the wizard after this shipped, which is how an
     * existing account never meets the guide. JSON, so it sits off the row.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'setup_guide')) {
            return;
        }

        // Short, as in the other users migrations: a busy table makes the deploy fail and retry
        // rather than queue every sign-in behind the ALTER.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->json('setup_guide')->nullable();
            });
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'setup_guide')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('setup_guide');
            });
        }
    }
};

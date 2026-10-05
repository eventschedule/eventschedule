<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The account-wide "Turn off suggestions" switch (App\Utils\SetupGuide::suggest()): when
     * set, the setup guide, the dashboard's next steps, a schedule's "List on the network"
     * prompt and the reminder emails that ask the same things all stay quiet until it is
     * cleared again. A timestamp rather than a flag, so the growth export can say when.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'suggestions_off_at')) {
            return;
        }

        // Short, as in the other users migrations: a busy table makes the deploy fail and retry
        // rather than queue every sign-in behind the ALTER.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('suggestions_off_at')->nullable();
            });
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'suggestions_off_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('suggestions_off_at');
            });
        }
    }
};

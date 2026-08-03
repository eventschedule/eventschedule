<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Why a custom domain failed to register with DigitalOcean, so the admin panel can show the
     * reason instead of a bare "Setup failed" that sends people to the logs.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // text, not string: the roles row is already close enough to MySQL's 65535-byte limit
            // that one more varchar(255) overflows it. TEXT is stored off-page, so it costs a
            // pointer. The service truncates before storing either way.
            $table->text('custom_domain_error')->nullable()->after('custom_domain_status');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('custom_domain_error');
        });
    }
};

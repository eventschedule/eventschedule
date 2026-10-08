<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * blocked_at: an operator has shut this account out (/admin/blocked). On the user's own row
     * because it is asked on every request the account makes (EnsureAccountNotBlocked), and the
     * row is already loaded there. Who blocked it, why, and which schedules the block took down
     * are in account_blocks.
     *
     * signup_ip: the address the account was created from, read through
     * RealtimeTracker::clientIp() and never from audit_logs.ip_address, which on an install
     * behind Cloudflare can hold an edge address shared by every visitor it serves. It is what
     * "refuse sign-ups from the address this account used" blocks, and it is removed after
     * PrunePersonalData::SIGNUP_IP_DAYS. Not in $fillable: a profile form must not be able to
     * post it.
     *
     * Deliberately no ->after(): users columns live in migrations dated out of order.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'signup_ip')) {
                $table->string('signup_ip', 45)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'signup_ip']);
        });
    }
};

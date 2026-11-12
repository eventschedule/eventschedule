<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Google half of the sign-up page. About 78% of the people who open /sign_up never make
     * an account, and only the email path was counted (signup_code_requests), so nothing could
     * say whether they left the page or left Google's consent screen. This counts a visitor who
     * pressed "Continue with Google" on the sign-up page, deduped per day like the code counters.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('marketing_daily_stats', 'signup_google_starts')) {
            Schema::table('marketing_daily_stats', function (Blueprint $table) {
                $table->unsignedInteger('signup_google_starts')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('marketing_daily_stats', 'signup_google_starts')) {
            Schema::table('marketing_daily_stats', function (Blueprint $table) {
                $table->dropColumn('signup_google_starts');
            });
        }
    }
};

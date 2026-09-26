<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The third counter on the 6-digit-code wall.
     *
     * signup_code_requests -> signup_code_verified lost about 38% in August 2026, and the pair
     * cannot say why: somebody who never came back from their inbox and somebody who came back
     * and typed a wrong code look identical. This counts the second kind - a visitor who had at
     * least one code rejected that day - deduped the same way as the other two.
     */
    public function up(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->unsignedInteger('signup_code_invalid')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('marketing_daily_stats', function (Blueprint $table) {
            $table->dropColumn('signup_code_invalid');
        });
    }
};

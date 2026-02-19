<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->timestamp('budget_alert_sent_at')->nullable()->after('meta_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropColumn('budget_alert_sent_at');
        });
    }
};

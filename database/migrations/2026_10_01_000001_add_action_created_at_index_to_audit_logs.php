<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * /admin/realtime's Activity card reads the last 24 hours of a handful of actions every ten
     * seconds (RealtimeActivity). The separate action and created_at indexes each leave MySQL
     * scanning one of the two ranges; together they bound it to exactly those rows.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action', 'created_at']);
        });
    }
};

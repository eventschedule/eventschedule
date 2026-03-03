<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'phone')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('email');
            });
        }

        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'ask_phone')) {
                $table->boolean('ask_phone')->default(false)->after('last_notified_fan_content_count');
            }
            if (! Schema::hasColumn('events', 'require_phone')) {
                $table->boolean('require_phone')->default(false)->after('ask_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['ask_phone', 'require_phone']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nullable with NO default: existing rows stay NULL (resolved to the "banner"
     * style in views), so live schedules are unchanged. New schedules default to
     * "compact" via the Role model's $attributes.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('header_style')->nullable()->after('design');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('header_style');
        });
    }
};

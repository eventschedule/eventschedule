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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('custom_domain')->nullable();
            $table->enum('event_layout', ['calendar', 'list', 'grid'])->default('calendar');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable();
            $table->string('google_token')->nullable();
            $table->string('google_refresh_token')->nullable();
            $table->timestamp('google_token_expires_at')->nullable();

            $table->string('facebook_id')->nullable();
            $table->string('facebook_token')->nullable();
            $table->timestamp('facebook_token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('custom_domain');
            $table->dropColumn('event_layout');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->dropColumn('google_token');
            $table->dropColumn('google_refresh_token');
            $table->dropColumn('google_token_expires_at');
            $table->dropColumn('facebook_id');
            $table->dropColumn('facebook_token');
            $table->dropColumn('facebook_token_expires_at');
        });
    }
};

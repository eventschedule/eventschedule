<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('feedback_enabled')->default(false)->after('direct_registration');
            $table->tinyInteger('feedback_delay_hours')->unsigned()->default(24)->after('feedback_enabled');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('feedback_enabled')->nullable()->after('rsvp_sold');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('feedback_sent_at')->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['feedback_enabled', 'feedback_delay_hours']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('feedback_enabled');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('feedback_sent_at');
        });
    }
};

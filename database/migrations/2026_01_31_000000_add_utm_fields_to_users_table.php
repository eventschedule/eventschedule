<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('utm_source', 255)->nullable()->after('language_code')->index();
            $table->string('utm_medium', 255)->nullable()->after('utm_source');
            $table->string('utm_campaign', 255)->nullable()->after('utm_medium')->index();
            $table->string('utm_content', 255)->nullable()->after('utm_campaign');
            $table->string('utm_term', 255)->nullable()->after('utm_content');
            $table->string('referrer_url', 2048)->nullable()->after('utm_term');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['utm_source']);
            $table->dropIndex(['utm_campaign']);
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer_url']);
        });
    }
};

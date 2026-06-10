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
            if (! Schema::hasColumn('roles', 'banner_enabled')) {
                $table->boolean('banner_enabled')->default(false)->after('description');
            }
            if (! Schema::hasColumn('roles', 'banner_on_event_pages')) {
                $table->boolean('banner_on_event_pages')->default(false)->after('banner_enabled');
            }
            if (! Schema::hasColumn('roles', 'banner_message')) {
                $table->text('banner_message')->nullable()->after('banner_on_event_pages');
            }
            if (! Schema::hasColumn('roles', 'banner_message_en')) {
                $table->text('banner_message_en')->nullable()->after('banner_message');
            }
            if (! Schema::hasColumn('roles', 'banner_message_html')) {
                $table->text('banner_message_html')->nullable()->after('banner_message_en');
            }
            if (! Schema::hasColumn('roles', 'banner_message_html_en')) {
                $table->text('banner_message_html_en')->nullable()->after('banner_message_html');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'banner_enabled',
                'banner_on_event_pages',
                'banner_message',
                'banner_message_en',
                'banner_message_html',
                'banner_message_html_en',
            ]);
        });
    }
};

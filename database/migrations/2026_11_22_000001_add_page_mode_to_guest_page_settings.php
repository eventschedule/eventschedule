<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a schedule's guest pages are drawn (Style > Background > Page style): 'auto' (light for a
 * light colour or gradient background, dark otherwise; App\Utils\GuestTheme::pageMode()), or
 * one of the six palettes (GuestPageSetting::MODES). On guest_page_settings, not `roles`, which is at MySQL's row-size limit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('guest_page_settings', 'page_mode')) {
            return;
        }

        Schema::table('guest_page_settings', function (Blueprint $table) {
            $table->string('page_mode', 8)->default('auto');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('guest_page_settings', 'page_mode')) {
            Schema::table('guest_page_settings', function (Blueprint $table) {
                $table->dropColumn('page_mode');
            });
        }
    }
};

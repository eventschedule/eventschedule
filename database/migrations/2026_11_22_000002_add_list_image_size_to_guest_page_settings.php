<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How large each event's picture is drawn in a schedule's list (Style > Events > Event pictures in
 * the list): 'small' (the list as it has been), 'medium' or 'large' (GuestPageSetting::LIST_IMAGE_SIZES).
 * On guest_page_settings, not `roles`, which is at MySQL's row-size limit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('guest_page_settings', 'list_image_size')) {
            return;
        }

        Schema::table('guest_page_settings', function (Blueprint $table) {
            $table->string('list_image_size', 8)->default('small');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('guest_page_settings', 'list_image_size')) {
            Schema::table('guest_page_settings', function (Blueprint $table) {
                $table->dropColumn('list_image_size');
            });
        }
    }
};

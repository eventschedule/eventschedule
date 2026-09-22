<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // The resized derivatives of profile_image_url, exactly as events.image_variants
            // records them for flyers: {"w480": "profile_abc_w480.webp", "w960": ...}, or a
            // recorded skip such as {"w480": null, "w960": null, "skipped": "too_large"}.
            //
            // A profile photo is the card image of every event without a flyer, so the homepage
            // wall served these originals (2MB PNGs) into 96px slots.
            //
            // Deliberately NOT fillable and NOT in BackupService::ROLE_EXPORT_FIELDS: a restore
            // holds none of the derivative files, so the restored schedule serves its original
            // until `images:backfill-variants --roles` regenerates them.
            $table->json('image_variants')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('image_variants');
        });
    }
};

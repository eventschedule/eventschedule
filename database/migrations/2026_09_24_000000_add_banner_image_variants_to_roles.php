<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // The resized derivatives of the two wide images a schedule can upload, recorded the
            // way image_variants records the profile photo's, plus the original's pixel size:
            // {"w960": "header_abc_w960.webp", "w1920": "header_abc_w1920.webp",
            //  "src": {"w": 3000, "h": 1500}}, or a recorded skip such as
            // {"w960": null, "w1920": null, "skipped": "too_large", "src": {...}}.
            //
            // The custom background is the schedule page's LCP image on a phone, and it was served
            // as the owner's original upload - a 1.1MB JPEG on the slowest page measured.
            //
            // A column each rather than one shared JSON: HasImageVariants::recordImageVariants()
            // rewrites the WHOLE column, guarded on one source column, so one save that replaces
            // both images queues two jobs whose writes would otherwise overwrite each other.
            //
            // Deliberately NOT fillable and NOT in BackupService::ROLE_EXPORT_FIELDS (an
            // allowlist): a restore holds none of the derivative files, so the restored schedule
            // serves its originals until `images:backfill-variants --roles --slot=all` rebuilds them.
            $table->json('header_image_variants')->nullable();
            $table->json('background_image_variants')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['header_image_variants', 'background_image_variants']);
        });
    }
};

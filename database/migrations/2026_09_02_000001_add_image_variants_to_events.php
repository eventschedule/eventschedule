<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Which resized derivatives of flyer_image_url exist on the storage disk, e.g.
            // {"w480": "flyer_abc123_w480.webp"}. A skip is recorded too, as
            // {"w480": null, "skipped": "too_large"}, so the backfill command is resumable and
            // does not re-download the same unusable original on every run.
            //
            // Recorded rather than derived because the alternative is a HEAD request to the CDN
            // per image per render: the homepage wall alone draws 25 of them, twice.
            //
            // Deliberately NOT fillable and NOT exported: BackupService walks getFillable() in
            // both directions, so a restored event comes back with this null and simply serves
            // its original until the backfill regenerates the derivative on the new install -
            // pointing a restore at derivative files that were never in the archive would 404
            // every card.
            $table->json('image_variants')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('image_variants');
        });
    }
};

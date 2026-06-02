<?php

use App\Utils\UrlUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Canonicalize existing guest-submitted YouTube URLs so no stored value
     * carries a guest-controlled query string (redirect/tracking payload).
     * Rows that do not parse to a valid video ID are left untouched.
     */
    public function up(): void
    {
        DB::table('event_videos')->orderBy('id')->chunkById(200, function ($videos) {
            foreach ($videos as $video) {
                $canonical = UrlUtils::getCanonicalYouTubeUrl($video->youtube_url);

                if ($canonical && $canonical !== $video->youtube_url) {
                    DB::table('event_videos')
                        ->where('id', $video->id)
                        ->update(['youtube_url' => $canonical]);
                }
            }
        });
    }

    public function down(): void
    {
        // No-op: original URLs are not recoverable.
    }
};

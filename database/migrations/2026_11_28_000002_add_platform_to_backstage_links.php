<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backstage_links', function (Blueprint $t) {
            $t->string('platform', 32)->nullable()->after('entry_id');
        });
        DB::statement(<<<'SQL'
            UPDATE backstage_links SET platform = CASE
                WHEN url LIKE '%spotify.com/%' THEN 'spotify'
                WHEN url LIKE '%music.apple.com/%' THEN 'apple_music'
                WHEN url LIKE '%bandcamp.com/%' THEN 'bandcamp'
                WHEN url LIKE '%soundcloud.com/%' THEN 'soundcloud'
                WHEN url LIKE '%instagram.com/%' THEN 'instagram'
                WHEN url LIKE '%facebook.com/%' THEN 'facebook'
                WHEN url LIKE '%tiktok.com/%' THEN 'tiktok'
                WHEN url LIKE '%linktr.ee/%' THEN 'linktree'
                ELSE 'linktree'
            END
            SQL);
        Schema::table('backstage_links', function (Blueprint $t) {
            $t->string('platform', 32)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('backstage_links', function (Blueprint $t) {
            $t->dropColumn('platform');
        });
    }
};

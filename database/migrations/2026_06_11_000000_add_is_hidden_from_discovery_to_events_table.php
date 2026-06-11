<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Platform-admin curation flag: hides an event from the platform-wide
            // discovery surfaces (homepage Discover, /browse, /search) without
            // unpublishing it. The event's own guest page stays public.
            $table->boolean('is_hidden_from_discovery')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_hidden_from_discovery');
        });
    }
};

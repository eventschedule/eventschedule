<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One Facebook account signs in to one user. The "already linked" checks in
     * SocialAuthController read then write, so only the index makes that hold under two
     * concurrent callbacks. Nothing has written facebook_id before Facebook Login, and MySQL
     * allows any number of NULLs in a unique index.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('facebook_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['facebook_id']);
        });
    }
};

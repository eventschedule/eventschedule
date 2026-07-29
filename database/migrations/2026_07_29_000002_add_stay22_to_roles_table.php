<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Per-schedule opt-in. Default off so an install that migrates and never
            // touches the admin portal shows nothing on any public page.
            $table->boolean('stay22_enabled')->default(false);

            // The schedule owner's own Stay22 affiliate ID. Nullable rather than
            // empty-string because null is meaningful: Stay22Service::resolveAid()
            // reads it as "fall back to the instance operator's ID".
            //
            // Deliberately not encrypted. Unlike users.invoiceninja_api_key this is not
            // a secret: it is interpolated into a public iframe URL that ships to every
            // visitor, so EncryptedString would cost a decrypt per guest render and
            // protect nothing.
            //
            // No ->after(): anchoring to a column created by a later-dated migration
            // breaks a fresh migrate, and column order in `roles` is already arbitrary.
            $table->string('stay22_aid', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['stay22_enabled', 'stay22_aid']);
        });
    }
};

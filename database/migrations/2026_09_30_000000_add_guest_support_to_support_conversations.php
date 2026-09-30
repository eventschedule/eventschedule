<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Support chat for signed-out visitors on the marketing site.
 *
 * A visitor has no account, so their conversation carries user_id = NULL and is found by a
 * random guest_token the browser keeps in localStorage. The support_conversations_user_id_unique
 * index keeps meaning "one conversation per account": MySQL treats NULLs as distinct, so any
 * number of guest rows can sit alongside it.
 *
 * guest_page is a PATH, never a full URL - it is rendered as a link in the admin inbox, and a
 * path cannot carry a javascript: scheme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_token', 64)->nullable()->unique();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_page')->nullable();
            $table->char('guest_country', 2)->nullable();
        });
    }

    public function down(): void
    {
        // A visitor's conversation has no account to fall back on, so it cannot survive the
        // column becoming NOT NULL again. support_messages cascades.
        \DB::table('support_conversations')->whereNull('user_id')->delete();

        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropUnique(['guest_token']);
            $table->dropColumn(['guest_token', 'guest_name', 'guest_email', 'guest_page', 'guest_country']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};

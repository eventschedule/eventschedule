<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where SendSupportReplyEmail got to, per conversation.
 *
 * It batches every unread admin reply into one email, and must not put the same reply in the
 * next email too. That marker has to live in the database: in the file cache it was wiped by
 * every deploy (App Platform starts each container empty), and the next email would then resend
 * everything still unread.
 *
 * Backfilled to each conversation's newest admin reply. Before this, every reply was emailed the
 * moment it was sent, and an account holder's replies were only ever marked read by opening the
 * widget - so without the backfill the first reply after the deploy would email months of them.
 *
 * The index serves the unread-from-customers count that the admin presence ping (every few
 * seconds per open AP tab) and AdminAlertService both run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('last_emailed_message_id')->nullable();
        });

        DB::table('support_conversations')->update([
            'last_emailed_message_id' => DB::raw(
                '(SELECT MAX(support_messages.id) FROM support_messages'
                .' WHERE support_messages.support_conversation_id = support_conversations.id'
                .' AND support_messages.is_from_admin = 1)'
            ),
        ]);

        Schema::table('support_messages', function (Blueprint $table) {
            $table->index(['is_from_admin', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropIndex(['is_from_admin', 'read_at']);
        });

        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropColumn('last_emailed_message_id');
        });
    }
};

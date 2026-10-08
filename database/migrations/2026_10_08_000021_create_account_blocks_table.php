<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per blocked account: who blocked it, the operator's own note, and the schedules the
 * block took down.
 *
 * role_ids is what makes Unblock an undo. A block marks every schedule the account owns as
 * deleted, and the account may have deleted some itself before that; only the ones listed here
 * are brought back. It lives here and not on roles, which is at MySQL's row size limit.
 *
 * The row is deleted by Unblock (the audit log keeps the history). users.blocked_at is the
 * same fact where every request can read it for nothing; AccountBlockService writes both in
 * one transaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->json('role_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_blocks');
    }
};

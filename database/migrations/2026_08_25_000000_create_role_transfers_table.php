<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ownership handovers (discussion #119): the owner nominates an email address,
     * the recipient signs in as that address and accepts, and only then does
     * roles.user_id move.
     *
     * A dedicated table rather than columns on `roles` because the row outlives the
     * swap: a declined or expired offer is worth keeping (it is the only record that
     * an owner tried to hand the schedule away), and `roles` is already very wide.
     *
     * There is no unique index on (role_id, status): MySQL cannot express "at most
     * one pending row", so the one-open-offer rule is enforced in
     * ScheduleTransferService::initiate(), which cancels any existing open row first.
     */
    public function up(): void
    {
        Schema::create('role_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('to_email');
            // Filled in on acceptance. Nullable because the recipient may not have an
            // account yet when the offer is sent.
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled'])->default('pending');
            // "Stay on as an admin" - only offered on Enterprise/selfhost, because Free
            // and Pro cannot hold a second team member.
            $table->boolean('keep_previous_owner')->default(false);
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            $table->index(['role_id', 'status']);
            $table->index(['to_email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_transfers');
    }
};

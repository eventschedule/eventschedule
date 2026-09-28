<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When this person started the card-free selling trial. The "once per owner" rule
            // cannot live only on roles.ticket_trial_ends_at: deleting a schedule removes the
            // row (Role has no soft deletes), so trial, delete, recreate would grant it again.
            // Not fillable.
            $table->timestamp('ticket_trial_used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ticket_trial_used_at');
        });
    }
};

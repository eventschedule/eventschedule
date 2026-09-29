<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // The schedule whose event_custom_fields definitions key events.custom_field_values.
            // Not creator_role_id: the values are written by whichever schedule's form saved them
            // (a venue editing a talent's event, the curator request flow), and every schedule's
            // first field key is new_0, so reading them against the wrong definitions both hides a
            // schedule's own values and can show another schedule's private value as public.
            // Null means "the creator", which is how every row written before this column reads.
            //
            // Deliberately no foreign key. nullOnDelete() would turn a deleted schedule's id into
            // null, and null falls back to the creator - reading the deleted schedule's answers
            // against the creator's fields, the exact cross-read this column exists to prevent.
            // A dangling id matches no schedule, so those values simply stay hidden.
            $table->unsignedBigInteger('custom_field_values_role_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['custom_field_values_role_id']);
            $table->dropColumn('custom_field_values_role_id');
        });
    }
};

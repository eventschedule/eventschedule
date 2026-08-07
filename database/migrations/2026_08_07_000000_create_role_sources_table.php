<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schedules a curator pulls events from. The reverse of roles.default_curator_ids,
     * which is set on the talent/venue and only reaches curators the same user owns.
     */
    public function up(): void
    {
        Schema::create('role_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->unique(['role_id', 'source_role_id']);

            // The hot lookup runs the other way: given an event's schedules, which
            // curators subscribe to them.
            $table->index('source_role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_sources');
    }
};

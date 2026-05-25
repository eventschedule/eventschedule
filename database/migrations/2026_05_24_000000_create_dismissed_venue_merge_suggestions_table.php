<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dismissed_venue_merge_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('venue_ids_hash', 64);
            $table->timestamps();

            $table->unique(['user_id', 'role_id', 'venue_ids_hash'], 'dvms_user_role_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dismissed_venue_merge_suggestions');
    }
};

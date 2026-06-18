<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dismissed_timezone_warnings')) {
            Schema::create('dismissed_timezone_warnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->string('events_hash', 64);
                $table->timestamps();

                $table->unique(['user_id', 'role_id', 'events_hash'], 'dtw_user_role_hash_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dismissed_timezone_warnings');
    }
};

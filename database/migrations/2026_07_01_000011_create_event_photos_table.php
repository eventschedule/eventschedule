<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_part_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('event_date')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('photo_url');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['event_id', 'is_approved']);
            $table->index(['event_part_id', 'is_approved']);
            $table->index(['event_id', 'event_date', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_photos');
    }
};

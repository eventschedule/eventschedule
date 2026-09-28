<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An organizer's photo gallery, on an event (event_id set) or on a schedule (event_id null).
 *
 * role_id is the schedule that OWNS the row: the schedule itself, or the event's owning schedule
 * (Event::ticketingRole()), whose plan decides whether the gallery is shown. A row with a
 * draft_token was uploaded from an edit form that has not been saved yet; GalleryUtils::sync()
 * commits it on Save and app:prune-gallery-drafts removes the ones that never are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename');
            $table->string('caption')->nullable();
            $table->string('credit', 100)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->char('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('image_variants')->nullable();
            $table->char('draft_token', 32)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'sort_order']);
            $table->index(['role_id', 'event_id', 'sort_order']);
            $table->index(['draft_token', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};

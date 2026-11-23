<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The vendor map: a plan of a market, fair or expo whose spots (stalls, tables, booths) are
     * taken by vendors, date by date.
     *
     * Vendors are NOT event participants (event_role). As participants the first vendor became
     * the event's URL host, its share image and its description, and a market with seventy
     * vendors would list them all as performers. Here they live on the map's own rows.
     *
     * One map per schedule, serving one event that schedule runs (a recurring season, or a
     * multi-day expo). The row IS the switch (`enabled`), never a column on `roles`, which is at
     * MySQL's row size limit.
     */
    public function up(): void
    {
        Schema::create('vendor_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            // market | craft_fair | flea_market | food_festival | expo: picks the full set of
            // headline strings (VendorMapKinds), never a word swapped into a sentence.
            $table->string('kind', 20)->default('market');
            // The event the map serves. nullOnDelete: a season that is deleted and made again
            // keeps the map and its vendors; the tab then asks which event to use.
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            // Private storage path of the image traced over in the designer (never public: a
            // photo of a market has people in it).
            $table->string('background_path')->nullable();
            // The owner's choice to show that picture under the shoppers' map (off by default:
            // a tracing photo is private).
            $table->boolean('background_public')->default(false);
            $table->unsignedSmallInteger('width')->default(1000);
            $table->unsignedSmallInteger('height')->default(700);
            // Bumped on every structure save; a save from an older revision is refused.
            $table->unsignedInteger('revision')->default(0);
            // The vendor link. Rotated by "Make a new link"; NULL until the link is first made.
            $table->string('claim_token', 48)->nullable()->unique();
            $table->boolean('claims_open')->default(true);
            // Where the printed entrance sign hangs ("You are here"), in map units.
            $table->unsignedSmallInteger('here_x')->nullable();
            $table->unsignedSmallInteger('here_y')->nullable();
            // The share card (1200x630 PNG) rendered on save, under a new name per revision,
            // because the public bucket's keys are cached as immutable.
            $table->string('share_image_path')->nullable();
            // Market days the organizer called off, with why (weather | holiday | other), so the
            // visitors' map can say "No market today (rain)". The event's excluded dates are the
            // fact; this only holds the reason.
            $table->json('cancelled_days')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_map_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_map_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('color', 7)->default('#4E81FA');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('vendor_map_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_map_id')->constrained()->cascadeOnDelete();
            // The number or code painted on the stall ("14", "B12").
            $table->string('label', 20);
            $table->foreignId('area_id')->nullable()->constrained('vendor_map_areas')->nullOnDelete();
            $table->smallInteger('x');
            $table->smallInteger('y');
            $table->unsignedSmallInteger('w');
            $table->unsignedSmallInteger('h');
            $table->smallInteger('rotation')->default(0);
            // Optional polygon (map units) for a spot that is not a rectangle.
            $table->json('shape')->nullable();
            // Power, water, size and the like. Stored, no controls yet.
            $table->json('attrs')->nullable();
            // A spot is never deleted while vendors have been on it: it is retired, so a past
            // date still says where somebody stood and a stall number is never reused by accident.
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_map_id', 'retired_at']);
        });

        Schema::create('vendor_map_landmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_map_id')->constrained()->cascadeOnDelete();
            // entrance | restroom | parking | stage | text
            $table->string('kind', 20);
            $table->string('label', 80)->nullable();
            $table->smallInteger('x');
            $table->smallInteger('y');
            $table->unsignedSmallInteger('w')->default(60);
            $table->unsignedSmallInteger('h')->default(40);
            $table->smallInteger('rotation')->default(0);
            $table->timestamps();
        });

        Schema::create('vendor_map_occupants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_map_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spot_id')->constrained('vendor_map_spots')->cascadeOnDelete();
            // '*' = every date of the map's event; a Y-m-d row overrides it for that date.
            $table->string('event_date', 10)->default('*');
            // present: the vendor is on this spot. absent: the season's vendor is NOT here on
            // this date ("Not coming this Friday"), which is how absent differs from free.
            $table->string('kind', 10)->default('present');
            // pending | approved | declined
            $table->string('status', 10)->default('pending');
            // The vendor's own page. NULL for a name only (read by the AI from a banner, typed by
            // the organizer, or a vendor for one day), which is never linked or pictured.
            $table->foreignId('vendor_role_id')->nullable()->constrained('roles')->cascadeOnDelete();
            $table->string('placeholder_name', 120)->nullable();
            $table->string('sells', 200)->nullable();
            $table->json('categories')->nullable();
            // Given by the vendor "so the organizer can reach you": shown to the schedule's
            // editors only, never in public data.
            $table->string('phone', 32)->nullable();
            // A claim's photo, private until approved; then a public thumbnail.
            $table->string('photo_path')->nullable();
            $table->string('photo_thumb_path')->nullable();
            // Who asked (the signed-in vendor), for "is this mine"; never shown.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 500)->nullable();
            // A row an approval stood aside ('replaced'): the claim that did it, so Undo can put it
            // back. Cleared once the vendor is told (the approval can no longer be undone).
            $table->unsignedBigInteger('replaced_by')->nullable()->index();
            $table->timestamp('decided_at')->nullable();
            // When a mail that names this row left (the owner's batched notice).
            $table->timestamp('notified_at')->nullable();
            // When the vendor's own mail about the decision left; held a couple of minutes so
            // an Undo really is one.
            $table->timestamp('vendor_notified_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_map_id', 'status']);
            $table->index(['vendor_map_id', 'event_date']);
            $table->index(['vendor_role_id', 'status']);
        });

        // MySQL has no partial unique index. A generated column that is 1 only for an approved
        // vendor on a spot (and NULL otherwise, which never collides) makes "one vendor per spot
        // per date" a constraint while any number of claims can wait on the same spot.
        DB::statement("ALTER TABLE vendor_map_occupants ADD COLUMN approved_slot TINYINT GENERATED ALWAYS AS (IF(status = 'approved' AND kind = 'present', 1, NULL)) STORED");
        DB::statement('CREATE UNIQUE INDEX vendor_map_occupants_one_per_spot ON vendor_map_occupants (spot_id, event_date, approved_slot)');

        Schema::create('vendor_map_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_map_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Private storage paths of the uploaded photos (and a PDF map).
            $table->json('images');
            // The AI's answer, kept so a phone that slept or a closed tab can come back to it.
            $table->json('result')->nullable();
            // uploaded | reading | ready | failed
            $table->string('status', 10)->default('uploaded');
            $table->timestamps();

            $table->index(['vendor_map_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_map_drafts');
        Schema::dropIfExists('vendor_map_occupants');
        Schema::dropIfExists('vendor_map_landmarks');
        Schema::dropIfExists('vendor_map_spots');
        Schema::dropIfExists('vendor_map_areas');
        Schema::dropIfExists('vendor_maps');
    }
};

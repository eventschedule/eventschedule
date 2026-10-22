<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A schedule's Aurora background (Style > Background > Aurora, App\Utils\Aurora).
 *
 * A table of its own, and not columns on `roles`, because `roles` is full: it sits at MySQL's
 * 65,535-byte row limit, and an ALTER adding one more column fails with errno 1118 (see
 * 2026_10_07_000005_create_venue_map_settings_table.php). It is also kept apart from
 * `roles.background_colors`, the gradient's colours, so switching between Aurora and Gradient
 * keeps each one's own choice.
 *
 * No row reads as an Aurora built from the schedule's accent, its arrangement taken from the
 * schedule's id, moving (Role::auroraSetting() has a default).
 *
 * palette: 'accent', 'header', 'custom' or a key of Aurora::PALETTES.
 * colors: '#rrggbb, #rrggbb, #rrggbb' for 'custom' (the owner's) and 'header' (sampled from the
 *   header picture by the server); null otherwise.
 * seed: the arrangement (Aurora::decode()); null falls back to the schedule's id.
 * motion: the owner's "Slow drift" switch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aurora_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('palette', 32)->default('accent');
            $table->string('colors', 64)->nullable();
            $table->unsignedSmallInteger('seed')->nullable();
            $table->boolean('motion')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurora_settings');
    }
};

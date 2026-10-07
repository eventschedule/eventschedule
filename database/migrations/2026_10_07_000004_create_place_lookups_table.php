<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where the venue map's pins come from (App\Services\PlaceLookupService).
 *
 * roles.geo_lat / geo_lon are Google's, and Google's terms allow them on a Google map only, so a
 * pin on the venue map comes from a second address search (config services.map.geocoder_url) and
 * is kept here. Keyed by the ADDRESS, not the venue: the search service blocks a client that
 * repeats a query, two venue rows at one address share an answer, and a venue that is merged,
 * deleted, restored or recreated by the demo reset needs nothing done.
 *
 * A row with no coordinates is an answer too: 'missing' means the service was asked and had
 * nothing usable. 'pending' is an address waiting its turn. Derived data, so not in backups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_lookups', function (Blueprint $table) {
            $table->id();
            $table->char('address_hash', 40)->unique();
            $table->string('address', 500);
            $table->char('country_code', 2)->nullable();
            $table->string('status', 12)->default('pending');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('try_after')->nullable();
            $table->timestamp('looked_up_at')->nullable();
            $table->timestamps();

            // What the runner reads: the oldest waiting addresses.
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_lookups');
    }
};

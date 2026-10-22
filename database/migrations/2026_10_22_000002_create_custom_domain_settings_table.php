<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which address a schedule served directly on its own domain calls its main one.
 *
 * main_address: 'custom' (the domain, as every direct schedule has always had it) or 'subdomain'
 * (the custom domain keeps serving every page, while the canonical, the sitemaps and every link
 * the app hands out name {subdomain}.eventschedule.com). See Role::mainAddressIsCustomDomain().
 *
 * A table of its own, and not a column on `roles`, because `roles` is full: it sits at MySQL's
 * 65,535-byte row limit, and an ALTER adding one more column fails with errno 1118 (see
 * 2026_10_07_000005_create_venue_map_settings_table). No row reads as 'custom', which is every
 * schedule until its owner chooses otherwise. The row stays when the domain is removed or the mode
 * changes, and applies again when Direct is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_domain_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('main_address', 16)->default('custom');
            $table->timestamps();

            // What CustomDomainSetting::subdomainFirstIds() reads on every request that asks.
            $table->index('main_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_domain_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sites that may be serving a copy of Event Schedule's marketing pages (App\Services\MarketingCopyWatch,
 * /admin/copies on the nexus). One row per host: a site that sent visitors to eventschedule.com, or
 * a federated install, and what the last look at its pages found.
 *
 * Hosts only: nothing about the visitors who came from them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_copy_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('host', 253)->charset('ascii')->collation('ascii_general_ci')->unique();
            // referrer or federation: how the host was first found.
            $table->string('source', 12);
            $table->unsignedInteger('visits')->default(0);
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at')->index();
            // unchecked, clear, copy, unreachable; dismissed and contacted are the operator's.
            $table->string('status', 12)->default('unchecked')->index();
            $table->dateTime('checked_at')->nullable();
            $table->unsignedTinyInteger('check_failures')->default(0);
            $table->json('markers')->nullable();
            $table->string('page_title', 200)->nullable();
            $table->string('checked_url', 300)->nullable();
            $table->dateTime('copy_found_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_copy_hosts');
    }
};

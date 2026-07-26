<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only populated on the nexus app (eventschedule.com), which registers the
        // installs that federate their events to it. Created everywhere for schema
        // consistency, like other nexus-only tables.
        Schema::create('federated_instances', function (Blueprint $table) {
            $table->id();
            $table->uuid('instance_id')->unique();   // self-issued by the participating install
            $table->string('site_url');              // the install's own base URL; event links must be on this host
            $table->string('name')->nullable();
            $table->string('contact_email')->nullable();
            // Encrypted, NOT hashed: verifying the request HMAC needs the plaintext back.
            $table->text('secret');
            $table->string('app_version', 32)->nullable();
            $table->string('status', 16)->default('pending');   // pending | approved | suspended
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            // Set when a push arrives claiming a different site_url than the one on
            // record, so an admin can look at a possible cloned or hijacked install.
            $table->timestamp('flagged_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('federated_instances');
    }
};

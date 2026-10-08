<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a new account is refused for: an email address, a whole email domain, or a network
 * address (one IP, or a range written as a CIDR). The operator's list at /admin/blocked.
 *
 * value is what the operator sees. match_key is what a sign-up is compared with: for an
 * address its canonical form (App\Services\Blocklist::canonicalEmail(), so a "+tag" or a dot in
 * a Gmail name does not make a new address), for a domain and a network address the same as
 * value.
 *
 * account_block_id: the block that added this entry, so Unblock removes what it added and
 * nothing the operator typed in. nullOnDelete, not cascade: when a blocked account is erased
 * its entries must stay, or its address is free to sign up again.
 *
 * refused_count / last_refused_at: how often the entry has turned a sign-up away, so the
 * operator can see that it does.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocklist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('value', 255);
            $table->string('match_key', 255);
            $table->string('note', 255)->nullable();
            $table->foreignId('account_block_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('refused_count')->default(0);
            $table->timestamp('last_refused_at')->nullable();
            $table->timestamps();

            $table->unique(['type', 'match_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocklist_entries');
    }
};

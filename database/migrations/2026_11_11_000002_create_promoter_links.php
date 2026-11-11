<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promoter links (the schedule's Promoters tab, Pro): a named code a schedule hands to a person who
 * sells for it. A visitor who arrives through `?via=CODE` on one of the schedule's guest pages and
 * then buys a ticket to one of the schedule's own events has that sale stamped with the link, so
 * the owner sees clicks, tickets, revenue and the commission earned per promoter.
 *
 * A table of its own, never a column on roles (at MySQL's row limit). sales.promoter_link_id has an
 * index and no foreign key, as events.ticket_preset_id: MySQL would copy the whole sales table to
 * add one, and a link is archived, never deleted, so the id it points at stays.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promoter_links')) {
            Schema::create('promoter_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->string('name', 100);
                // What goes after ?via=. Unique per schedule only: two schedules may each have a "sam".
                $table->string('code', 40);
                $table->decimal('commission_percent', 5, 2)->nullable();
                $table->unsignedInteger('clicks')->default(0);
                // Set by the first counted click; from then on the code is printed on somebody's
                // flyers and cannot be renamed.
                $table->timestamp('first_click_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();

                $table->unique(['role_id', 'code']);
            });
        }

        if (! Schema::hasColumn('sales', 'promoter_link_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->unsignedBigInteger('promoter_link_id')->nullable();
                $table->index('promoter_link_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['promoter_link_id']);
            $table->dropColumn('promoter_link_id');
        });

        Schema::dropIfExists('promoter_links');
    }
};

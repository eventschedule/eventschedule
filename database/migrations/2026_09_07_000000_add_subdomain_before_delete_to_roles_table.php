<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin take a squatted subdomain back.
 *
 * roles.subdomain is UNIQUE, so exactly one row can hold a name. Marking a schedule deleted did
 * not free it: Role::scopeSubdomain(), Role::generateSubdomain() and RoleController::update()'s
 * collision check all ignore is_deleted, and the index would reject a duplicate anyway. The only
 * thing that ever freed a name was the owner's hard delete, which cascades roughly twenty child
 * tables and destroys revenue history - the wrong tool for cleaning up a junk schedule.
 *
 * So a released row is RENAMED to {name}-deleted-{id}, and this column remembers what it was
 * called, which is what lets Restore offer the original name back when nothing else has claimed
 * it in the meantime. Null means either never deleted, or deleted without a release (the API,
 * unfollow and merge paths all soft-delete and keep their names).
 *
 * Deliberately NOT added to BackupService::ROLE_EXPORT_FIELDS. That is an explicit allow-list
 * which both exportRole() and importRole() iterate, and this is bookkeeping about one install's
 * namespace - an import assigns a fresh subdomain regardless. is_deleted is absent for the same
 * reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // text(), not string(). `roles` has 171 columns and ~64.7 KB of varchar, against
            // MySQL's hard 65,535-byte row limit - a varchar(255) costs 1,020 bytes at utf8mb4
            // and the ALTER fails outright with errno 1118. TEXT stores off-page and costs about
            // 20 bytes of pointer, which fits. `approved_subdomains` is TEXT for the same reason.
            //
            // It also happens to be the more correct type: subdomains longer than the
            // 50-character cap in Role::cleanSubdomain() were grandfathered in before the cap
            // existed (see SitemapController::isListable), and a varchar(50) would throw on one
            // in strict mode rather than round-trip it.
            //
            // No index: the column is only read on one admin edit page, one row at a time.
            $table->text('subdomain_before_delete')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('subdomain_before_delete');
        });
    }
};

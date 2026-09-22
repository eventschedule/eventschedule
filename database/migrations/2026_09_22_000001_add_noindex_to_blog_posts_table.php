<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An admin's "keep this post out of the index" switch.
 *
 * The blog holds a couple of hundred AI-generated posts, and a thin or formulaic one is a
 * liability on the host that links to the product (Google's scaled-content policy). Deleting a
 * post throws away its backlinks and 404s its URL; this keeps it live and linkable while the page
 * says `noindex, follow` and the blog sitemap leaves it out.
 *
 * Defaults to false so every existing post stays indexed: the admin list's word count column is
 * how they get triaged, one at a time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->boolean('noindex')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('noindex');
        });
    }
};

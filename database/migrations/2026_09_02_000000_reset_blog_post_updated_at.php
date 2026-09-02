<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time repair of blog_posts.updated_at.
 *
 * BlogPost::incrementViewCount() used to run Eloquent's increment(), which appends
 * updated_at = now() to the UPDATE, so every anonymous page view restamped the row. 161 of 187
 * published posts ended up reporting <lastmod>, dateModified and article:modified_time equal to
 * the last crawl, which trains Google to ignore the signal site-wide.
 *
 * The true edit dates are gone. The publish date is the honest fallback, floored at created_at so
 * a back-dated published_at cannot claim the post existed before the row did.
 *
 * The expression reads nothing from updated_at, so re-running is a no-op, and an empty table
 * updates zero rows. created_at is required because GREATEST() returns NULL if any argument is
 * NULL, which would blank the column on a legacy row.
 *
 * view_count > 0 is what keeps this off the rows that were never damaged. The restamp came from
 * exactly one statement - BlogPost::incrementViewCount(), whose only caller is BlogController's
 * public-view branch - and that is the SAME statement that increments the counter. So a post at
 * zero views was never touched by the bug and its updated_at is a real edit date; overwriting it
 * with published_at would destroy the very signal this migration exists to protect, on the subset
 * of rows that were already correct.
 *
 * It is not a perfect discriminator: a post that was viewed and then genuinely edited is still
 * dragged back, because nothing recorded when the edit happened. That residual is unavoidable
 * without an audit trail; the filter removes the part that is avoidable.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('blog_posts')
            ->where('is_published', true)
            ->whereNotNull('created_at')
            ->where('view_count', '>', 0)
            ->update([
                'updated_at' => DB::raw('GREATEST(COALESCE(published_at, created_at), created_at)'),
            ]);
    }

    public function down(): void
    {
        // The values this replaced were crawl timestamps, not edit dates. Nothing to restore.
    }
};

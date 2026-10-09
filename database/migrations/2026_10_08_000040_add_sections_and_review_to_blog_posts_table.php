<?php

use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The blog after its 2026-10 review.
     *
     * - `category`: one fixed section a post (BlogPost::CATEGORIES), in place of five free tags,
     *   of which 222 posts had collected 641. Every existing post is filed here by its slug,
     *   title and tags (BlogPost::guessCategory()), because the index, the section pages and
     *   "Keep reading" all read this column.
     * - `primary_query`, `faq`: the search a generated post answers and the questions it ends on.
     * - `source`, `held_reason`: which generator wrote a post, and why the check held it as a
     *   draft when it did. A rejected post used to be thrown away.
     * - `redirect_slug`: a post merged into another answers with a 301 to it.
     * - `review`: what the review of the existing posts found for this one, and what is proposed.
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('category', 40)->nullable()->index();
            $table->string('primary_query')->nullable();
            $table->json('faq')->nullable();
            $table->string('source', 20)->nullable();
            $table->text('held_reason')->nullable();
            $table->string('redirect_slug')->nullable();
            $table->json('review')->nullable();
        });

        // updated_at is a post's dateModified and its sitemap lastmod: filing a post under a
        // section is not an edit, so the stamp is left exactly as it was.
        DB::table('blog_posts')->orderBy('id')->select(['id', 'slug', 'title', 'tags'])->chunkById(200, function ($posts) {
            foreach ($posts as $post) {
                $tags = json_decode((string) $post->tags, true);

                DB::table('blog_posts')->where('id', $post->id)->update([
                    'category' => BlogPost::guessCategory((string) $post->slug, (string) $post->title, is_array($tags) ? array_values(array_filter($tags, 'is_string')) : []),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['category', 'primary_query', 'faq', 'source', 'held_reason', 'redirect_slug', 'review']);
        });
    }
};

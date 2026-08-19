<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operator-authored replacements for the built-in privacy policy, terms of
     * service and cookie policy (issue #116). One row per document type; the
     * absence of a row means "use the built-in page".
     *
     * Deliberately NOT the `settings` table: Setting::get() caches that whole
     * table as a single map with rememberForever, and it is read on nearly every
     * request, so three long documents there would be unserialized on every page
     * load. A dedicated table also gives updated_at for the "Last updated" stamp.
     */
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            // An externally hosted policy. Takes precedence over `content`.
            $table->string('url')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_html')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};

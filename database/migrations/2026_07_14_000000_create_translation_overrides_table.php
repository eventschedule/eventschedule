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
        Schema::create('translation_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            // `group` and `key` are MySQL reserved-ish words; the query builder
            // backticks them, but never reference them in raw SQL fragments.
            $table->string('group', 20);   // messages | accessibility | marketing
            $table->string('key', 191);
            $table->text('value');
            // When this override was last shared with the nexus app; null = unshared.
            $table->timestamp('shared_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_overrides');
    }
};

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
        // Only populated on the nexus app (eventschedule.com), which receives
        // translation suggestions shared by other installs. Created everywhere
        // for schema consistency, like other nexus/hosted-only tables.
        Schema::create('translation_suggestions', function (Blueprint $table) {
            $table->id();
            $table->uuid('instance_id');   // anonymous, self-issued by the sharing install
            $table->string('locale', 10);
            $table->string('group', 20);
            $table->string('key', 191);
            $table->text('suggested_value');
            // The shipped translation on the sharing install at the time of sharing,
            // so the reviewer can spot suggestions made against outdated strings.
            $table->text('shipped_value')->nullable();
            $table->string('app_version', 32)->nullable();
            $table->string('status', 16)->default('pending');   // pending | approved | rejected
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'locale', 'group', 'key'], 'translation_suggestions_instance_key_unique');
            $table->index(['status', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_suggestions');
    }
};

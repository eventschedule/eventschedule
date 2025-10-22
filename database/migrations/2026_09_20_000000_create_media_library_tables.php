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
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folder')->nullable();
            $table->timestamps();
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('media_asset_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['media_asset_id', 'media_tag_id']);
        });

        Schema::create('media_asset_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('label')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->json('crop_meta')->nullable();
            $table->timestamps();
        });

        Schema::create('media_asset_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->foreignId('media_asset_variant_id')->nullable()->constrained('media_asset_variants')->nullOnDelete();
            $table->string('context')->nullable();
            $table->morphs('usable');
            $table->timestamps();
            $table->unique([
                'media_asset_id',
                'media_asset_variant_id',
                'usable_type',
                'usable_id',
                'context',
            ], 'media_asset_usages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_asset_usages');
        Schema::dropIfExists('media_asset_variants');
        Schema::dropIfExists('media_asset_tag');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_assets');
    }
};

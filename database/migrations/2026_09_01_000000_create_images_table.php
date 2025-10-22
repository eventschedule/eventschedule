<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk')->default('images');
            $table->string('directory')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size');
            $table->string('path');
            $table->json('variants')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('reference_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('checksum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};

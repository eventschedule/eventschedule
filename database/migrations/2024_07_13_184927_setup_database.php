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
        Schema::dropIfExists('role_images');
        Schema::dropIfExists('event_images');
        Schema::dropIfExists('events');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('visibility', ['private', 'unlisted', 'public'])->default('private');
            $table->dateTime('published_at')->nullable();
            $table->boolean('use_24_hour_time')->default(false);
            $table->boolean('accept_talent_requests')->default(true);
            $table->boolean('accept_vendor_requests')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('subdomain')->unique();
            $table->enum('type', ['venue', 'talent', 'vendor'])->index();
            $table->string('design')->default('clean');
            $table->enum('background', ['default', 'image', 'gradient'])->default('default');
            $table->string('background_colors')->nullable();
            $table->integer('background_rotation')->nullable();
            $table->string('font_color')->nullable();
            $table->string('font_name')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->text('postal_code')->nullable();
            $table->string('country_code')->nullable();
            $table->text('description')->nullable();
            $table->text('social_links')->nullable();
            $table->text('payment_links')->nullable();
            $table->text('youtube_links')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('background_image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->enum('level', ['owner', 'admin', 'follower'])->index();
            $table->timestamps();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained('roles')->onDelete('cascade');
            $table->enum('visibility', ['private', 'unlisted', 'public'])->default('private');
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_accepted')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->float('duration', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_images');
        Schema::dropIfExists('event_images');
        Schema::dropIfExists('events');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};

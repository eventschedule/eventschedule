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
            $table->boolean('is_unlisted')->default(false);
            $table->boolean('use_24_hour_time')->default(false);
            $table->boolean('accept_talent_requests')->default(true);
            $table->boolean('accept_vendor_requests')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('subdomain')->unique();
            $table->enum('type', ['venue', 'talent', 'vendor'])->index();
            $table->enum('background', ['color', 'image', 'gradient'])->default('color');
            $table->string('accent_color')->default('#007bff');
            $table->string('background_color')->default('#ffffff');
            $table->string('background_colors')->nullable();
            $table->integer('background_rotation')->default(150);
            $table->string('font_color')->default('#111827');
            $table->string('font_family')->default('Roboto');
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable();            
            $table->string('formatted_address')->nullable();
            $table->string('google_place_id')->nullable();
            $table->string('geo_address')->nullable();
            $table->string('geo_lat')->nullable();
            $table->string('geo_lon')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language_code')->default('en');
            $table->text('description')->nullable();
            $table->text('description_html')->nullable();
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
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_accepted')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->float('duration', 8, 3)->nullable();
            $table->text('description')->nullable();
            $table->text('description_html')->nullable();
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

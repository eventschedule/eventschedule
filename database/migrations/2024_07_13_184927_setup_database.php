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
        Schema::dropIfExists('events');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subdomain')->unique();
            $table->enum('role', ['venue', 'talent', 'vendor'])->index();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('country_id');
            $table->text('description');
            $table->string('website_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('x_url')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained('roles')->onDelete('cascade');
            $table->enum('role', ['talent', 'vendor']);
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->string('start_time')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};

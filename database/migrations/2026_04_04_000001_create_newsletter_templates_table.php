<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('blocks')->nullable();
            $table->json('style_settings')->nullable();
            $table->string('template', 50)->default('modern');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('test_field', 50);
            $table->unsignedInteger('sample_percentage')->default(20);
            $table->string('winner_criteria', 50)->default('open_rate');
            $table->unsignedInteger('winner_wait_hours')->default(4);
            $table->timestamp('winner_selected_at')->nullable();
            $table->char('winner_variant', 1)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->json('blocks')->nullable();
            $table->json('style_settings')->nullable();
            $table->string('template', 50)->default('modern');
            $table->json('event_ids')->nullable();
            $table->json('segment_ids')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->foreignId('ab_test_id')->nullable()->constrained('newsletter_ab_tests')->nullOnDelete();
            $table->char('ab_variant', 1)->nullable();
            $table->string('send_token', 64)->unique()->nullable();
            $table->timestamps();

            $table->index(['role_id', 'status']);
            $table->index(['scheduled_at', 'status']);
        });

        Schema::create('newsletter_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->unsignedInteger('click_count')->default(0);

            $table->index(['newsletter_id', 'status']);
            $table->index(['token']);
        });

        Schema::create('newsletter_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_recipient_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048);
            $table->timestamp('clicked_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->index(['newsletter_recipient_id']);
        });

        Schema::create('newsletter_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50);
            $table->json('filter_criteria')->nullable();
            $table->timestamps();

            $table->index(['role_id']);
        });

        Schema::create('newsletter_segment_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_segment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['newsletter_segment_id']);
        });

        Schema::create('newsletter_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('unsubscribed_at');

            $table->unique(['role_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_unsubscribes');
        Schema::dropIfExists('newsletter_segment_users');
        Schema::dropIfExists('newsletter_segments');
        Schema::dropIfExists('newsletter_clicks');
        Schema::dropIfExists('newsletter_recipients');
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('newsletter_ab_tests');
    }
};

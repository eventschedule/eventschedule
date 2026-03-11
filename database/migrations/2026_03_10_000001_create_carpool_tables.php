<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('carpool_enabled')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('carpool_agreed_at')->nullable();
            $table->boolean('carpool_notifications_enabled')->default(true);
        });

        Schema::create('carpool_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->date('event_date')->nullable();
            $table->string('direction', 20)->default('to_event');
            $table->string('city', 255);
            $table->time('departure_time')->nullable();
            $table->string('meeting_point', 255)->nullable();
            $table->unsignedTinyInteger('total_spots');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['event_id', 'event_date', 'status']);
            $table->index('user_id');
        });

        Schema::create('carpool_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpool_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['carpool_offer_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('carpool_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpool_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['carpool_offer_id', 'reviewer_user_id', 'reviewed_user_id'], 'carpool_reviews_offer_reviewer_reviewed_unique');
            $table->index('reviewer_user_id');
            $table->index('reviewed_user_id');
        });

        Schema::create('carpool_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carpool_offer_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->timestamps();

            $table->unique(['carpool_offer_id', 'reporter_user_id', 'reported_user_id'], 'carpool_reports_offer_reporter_reported_unique');
            $table->index('reporter_user_id');
            $table->index('reported_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpool_reports');
        Schema::dropIfExists('carpool_reviews');
        Schema::dropIfExists('carpool_requests');
        Schema::dropIfExists('carpool_offers');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['carpool_agreed_at', 'carpool_notifications_enabled']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('carpool_enabled');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('slot_interval_minutes')->nullable();
            $table->unsignedSmallInteger('buffer_before_minutes')->default(0);
            $table->unsignedSmallInteger('buffer_after_minutes')->default(0);
            $table->unsignedSmallInteger('min_notice_hours')->default(0);
            $table->unsignedSmallInteger('max_advance_days')->default(60);

            // Wall-clock windows in the role's timezone. Keys "0"-"6" (0=Sunday, Carbon dayOfWeek),
            // each an array of {"start":"HH:MM","end":"HH:MM"} ranges.
            $table->json('weekly_windows');
            // Per-date overrides keyed "Y-m-d". A present key REPLACES the weekly windows for that
            // date; an empty array means the date is closed.
            $table->json('date_overrides')->nullable();

            $table->string('location_type', 20)->default('in_person'); // in_person|online|phone
            $table->string('location_address', 500)->nullable();
            $table->string('location_url', 500)->nullable();
            $table->string('location_phone', 50)->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency_code', 3)->nullable();
            $table->string('payment_method', 20)->nullable(); // stripe|payment_url|cash (ignored when free)

            $table->boolean('requires_approval')->default(false);
            $table->unsignedSmallInteger('capacity')->default(1); // future; v1 always books 1
            $table->json('custom_fields')->nullable(); // same shape as events.custom_fields
            $table->boolean('ask_phone')->default(false);
            $table->boolean('require_phone')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);

            $table->timestamps();

            $table->index(['role_id', 'is_active', 'is_deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_types');
    }
};

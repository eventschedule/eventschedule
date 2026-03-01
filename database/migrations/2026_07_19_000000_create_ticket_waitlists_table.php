<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('event_date');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('subdomain');
            $table->enum('status', ['waiting', 'notified', 'purchased', 'expired'])->default('waiting');
            $table->string('locale', 10)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'event_date', 'email']);
            $table->index(['event_id', 'event_date', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_waitlists');
    }
};

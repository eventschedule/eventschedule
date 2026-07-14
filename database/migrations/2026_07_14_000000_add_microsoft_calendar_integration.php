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
        Schema::table('users', function (Blueprint $table) {
            $table->string('microsoft_id')->nullable()->index();
            $table->text('microsoft_token')->nullable();          // Encrypted access token (Graph JWTs are large)
            $table->text('microsoft_refresh_token')->nullable();  // Encrypted; Microsoft rotates this on every refresh
            $table->timestamp('microsoft_token_expires_at')->nullable();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->enum('microsoft_sync_direction', ['to', 'from', 'both'])->nullable();
            $table->string('microsoft_webhook_id')->nullable();          // Graph subscription id
            $table->timestamp('microsoft_webhook_expires_at')->nullable();
            $table->text('microsoft_sync_token')->nullable();            // Graph @odata.deltaLink (exceeds 255 chars)
            $table->timestamp('microsoft_last_sync_at')->nullable();
            $table->boolean('microsoft_create_teams_meetings')->default(false);
            $table->index('microsoft_sync_direction');
        });

        Schema::table('role_user', function (Blueprint $table) {
            // 512 to match microsoft_calendar_syncs; Graph calendar ids are long base64url strings.
            $table->string('microsoft_calendar_id', 512)->nullable()->after('google_calendar_id');
        });

        Schema::create('microsoft_calendar_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('microsoft_event_id', 512)->nullable();     // Graph event ids are long
            $table->string('microsoft_calendar_id', 512)->nullable();  // Calendar the copy landed on
            $table->timestamps();

            $table->unique(['user_id', 'event_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('microsoft_calendar_syncs');

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropColumn('microsoft_calendar_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['microsoft_sync_direction']);
            $table->dropColumn([
                'microsoft_sync_direction',
                'microsoft_webhook_id',
                'microsoft_webhook_expires_at',
                'microsoft_sync_token',
                'microsoft_last_sync_at',
                'microsoft_create_teams_meetings',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'microsoft_id',
                'microsoft_token',
                'microsoft_refresh_token',
                'microsoft_token_expires_at',
            ]);
        });
    }
};

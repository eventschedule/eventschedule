<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'flyer_image_id')) {
                $table->foreignId('flyer_image_id')->nullable()->constrained('images')->nullOnDelete();
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'profile_image_id')) {
                $table->foreignId('profile_image_id')->nullable()->constrained('images')->nullOnDelete();
            }

            if (! Schema::hasColumn('roles', 'header_image_id')) {
                $table->foreignId('header_image_id')->nullable()->constrained('images')->nullOnDelete();
            }

            if (! Schema::hasColumn('roles', 'background_image_id')) {
                $table->foreignId('background_image_id')->nullable()->constrained('images')->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_image_id')) {
                $table->foreignId('profile_image_id')->nullable()->constrained('images')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'flyer_image_id')) {
                $table->dropConstrainedForeignId('flyer_image_id');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'profile_image_id')) {
                $table->dropConstrainedForeignId('profile_image_id');
            }

            if (Schema::hasColumn('roles', 'header_image_id')) {
                $table->dropConstrainedForeignId('header_image_id');
            }

            if (Schema::hasColumn('roles', 'background_image_id')) {
                $table->dropConstrainedForeignId('background_image_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_image_id')) {
                $table->dropConstrainedForeignId('profile_image_id');
            }
        });
    }
};

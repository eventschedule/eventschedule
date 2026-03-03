<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 8)->nullable()->unique()->after('landing_page');
            $table->unsignedBigInteger('referred_by_user_id')->nullable()->after('referral_code');
            $table->foreign('referred_by_user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->unsignedBigInteger('referred_role_id')->nullable();
            $table->enum('plan_type', ['pro', 'enterprise'])->nullable();
            $table->enum('status', ['pending', 'subscribed', 'qualified', 'credited', 'expired'])->default('pending');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->unsignedBigInteger('credited_role_id')->nullable();
            $table->timestamps();

            $table->foreign('referrer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_role_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('credited_role_id')->references('id')->on('roles')->onDelete('set null');

            $table->unique('referred_user_id');
            $table->index(['referrer_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropColumn(['referral_code', 'referred_by_user_id']);
        });
    }
};

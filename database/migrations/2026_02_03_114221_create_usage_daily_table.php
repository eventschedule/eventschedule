<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('operation', 50);
            $table->unsignedBigInteger('role_id')->default(0);
            $table->unsignedInteger('count')->default(0);

            $table->unique(['date', 'operation', 'role_id']);
            $table->index('date');
            $table->index('role_id');
            $table->index('operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_daily');
    }
};

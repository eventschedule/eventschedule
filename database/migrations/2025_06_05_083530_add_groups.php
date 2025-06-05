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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->nullable();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->unique(['slug', 'role_id']);
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable();
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->dropColumn('category_id');        
        });

        Schema::dropIfExists('groups');
    }
};

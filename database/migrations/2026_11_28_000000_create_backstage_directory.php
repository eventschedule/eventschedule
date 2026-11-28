<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backstage_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('schedule_id')->constrained('roles')->cascadeOnDelete();
            $t->foreignId('participant_id')->constrained('roles')->cascadeOnDelete();
            $t->unique(['schedule_id', 'participant_id']);
            $t->text('promotion')->nullable();
        });
        Schema::create('backstage_contacts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('entry_id')->constrained('backstage_entries')->cascadeOnDelete();
            $t->string('name');
            $t->string('label')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->text('website')->nullable();
        });
        Schema::create('backstage_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('entry_id')->constrained('backstage_entries')->cascadeOnDelete();
            $t->text('url');
        });
        Schema::create('backstage_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('entry_id')->constrained('backstage_entries')->cascadeOnDelete();
            $t->string('label')->nullable();
            $t->string('filename');
            $t->unsignedBigInteger('bytes');
        });
        // Private across containers, including on installs whose media disk is public. Bounded
        // chunks stay under MySQL's packet limit and cascade away atomically on schedule erasure.
        Schema::create('backstage_file_chunks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('file_id')->constrained('backstage_files')->cascadeOnDelete();
            $t->binary('contents');
        });
    }

    public function down(): void
    {
        foreach (['file_chunks', 'files', 'links', 'contacts', 'entries'] as $table) {
            Schema::dropIfExists('backstage_'.$table);
        }
    }
};

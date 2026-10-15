<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per account an operator looked at on /admin/blocked and marked not spam, although a
 * watched word is in its name or in something it made (SpamWatch). The account is not flagged
 * again; deleting the row ("Flag again") puts it back in the list.
 *
 * Its own table and not a column, as account_blocks is: nothing on a request reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spam_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spam_clearances');
    }
};

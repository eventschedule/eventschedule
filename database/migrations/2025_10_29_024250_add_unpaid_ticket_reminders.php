<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->integer('remind_unpaid_tickets_every')->default(0);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('remind_unpaid_tickets_every');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });
    }
};

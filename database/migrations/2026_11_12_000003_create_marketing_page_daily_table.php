<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_page_daily', function (Blueprint $table) {
            $table->id();
            // UTC day. Unique with page and device so MarketingPageDaily::record()'s
            // "INSERT ... ON DUPLICATE KEY UPDATE" counter never creates duplicates.
            $table->date('date');
            // The marketing ROUTE NAME (marketing.pricing, blog.show), never a path from the client.
            $table->string('page', 100);
            // phone or desktop, from the viewport width the page's beacon sends.
            $table->string('device', 10);
            // Each one visitor per page per day (IP+UA daily hash), counts only.
            $table->unsignedInteger('visitors')->default(0);
            $table->unsignedInteger('reached_end')->default(0);
            $table->unsignedInteger('signup_clicks')->default(0);
            $table->unique(['date', 'page', 'device']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_page_daily');
    }
};

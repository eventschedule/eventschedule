<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_ticket_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_ticket_id')->constrained()->onDelete('cascade');
            $table->string('secret')->unique();
            $table->unsignedInteger('seat_number');
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
        });

        DB::table('sale_tickets')->orderBy('id')->chunkById(100, function ($saleTickets) {
            foreach ($saleTickets as $saleTicket) {
                $seats = [];

                try {
                    $decoded = json_decode($saleTicket->seats, true);

                    if (is_array($decoded)) {
                        $seats = $decoded;
                    }
                } catch (\Throwable $exception) {
                    $seats = [];
                }

                if ($seats === [] && $saleTicket->quantity > 0) {
                    $seats = array_fill(0, $saleTicket->quantity, null);
                }

                foreach (array_values($seats) as $index => $value) {
                    $scannedAt = null;

                    if ($value) {
                        $timestamp = is_numeric($value) ? (int) $value : null;

                        if ($timestamp) {
                            $scannedAt = Carbon::createFromTimestamp($timestamp);
                        }
                    }

                    DB::table('sale_ticket_entries')->insert([
                        'sale_ticket_id' => $saleTicket->id,
                        'secret' => Str::lower(Str::random(32)),
                        'seat_number' => $index + 1,
                        'scanned_at' => $scannedAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn('seats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->text('seats')->nullable();
        });

        DB::table('sale_tickets')->orderBy('id')->chunkById(100, function ($saleTickets) {
            foreach ($saleTickets as $saleTicket) {
                $entries = DB::table('sale_ticket_entries')
                    ->where('sale_ticket_id', $saleTicket->id)
                    ->orderBy('seat_number')
                    ->get();

                $encoded = $entries->mapWithKeys(function ($entry) {
                    return [
                        $entry->seat_number => $entry->scanned_at
                            ? Carbon::parse($entry->scanned_at)->timestamp
                            : null,
                    ];
                })->toArray();

                DB::table('sale_tickets')
                    ->where('id', $saleTicket->id)
                    ->update([
                        'seats' => json_encode($encoded),
                    ]);
            }
        });

        Schema::dropIfExists('sale_ticket_entries');
    }
};

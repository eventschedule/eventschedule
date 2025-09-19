<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class ReleaseTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('Release expired unpaid tickets...');
        
        $candidateSales = Sale::where('status', 'unpaid')
            ->where('is_deleted', false)
            ->whereHas('event', function ($query) {
                $query->where('events.expire_unpaid_tickets', '>', 0);
            })
            ->with(['event', 'saleTickets.ticket'])
            ->get();

        $expiredSales = $candidateSales->filter(function (Sale $sale) {
            $expireAfterHours = (int) $sale->event->expire_unpaid_tickets;

            if ($expireAfterHours <= 0 || ! $sale->created_at) {
                return false;
            }

            $expiresAt = $sale->created_at->copy()->addHours($expireAfterHours);

            return Carbon::now()->greaterThanOrEqualTo($expiresAt);
        });

        \Log::info('Found ' . $expiredSales->count() . ' expired sales to process');

        foreach ($expiredSales as $sale) {
            $sale->status = 'expired';
            $sale->save();
        }
    }
}

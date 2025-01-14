<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;

class release_tickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release_tickets';

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
        
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        
        $expiredSales = Sale::where('status', 'pending')
            ->whereHas('event', function($query) use ($driver) {
                $query->where('events.expire_unpaid_tickets', '>', 0);
                if ($driver === 'sqlite') {
                    $query->whereRaw("(strftime('%s', 'now') - strftime('%s', sales.created_at))/3600 > events.expire_unpaid_tickets");
                } else {
                    $query->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) > events.expire_unpaid_tickets');
                }
            })
            ->get();
        
        \Log::info('Found ' . $expiredSales->count() . ' expired sales to process');

        foreach ($expiredSales as $sale) {
            $sale->status = 'expired';
            $sale->save();
        }
    }
}

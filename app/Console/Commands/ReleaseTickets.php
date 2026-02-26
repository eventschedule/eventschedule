<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Utils\InvoiceNinja;
use Illuminate\Console\Command;

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

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        $expiredSales = Sale::where('status', 'unpaid')
            ->whereHas('event', function ($query) use ($driver) {
                $query->where('events.expire_unpaid_tickets', '>', 0);
                if ($driver === 'sqlite') {
                    $query->whereRaw("(strftime('%s', 'now') - strftime('%s', sales.created_at))/3600 >= events.expire_unpaid_tickets");
                } else {
                    $query->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) >= events.expire_unpaid_tickets');
                }
            })
            ->get();

        \Log::info('Found '.$expiredSales->count().' expired sales to process');

        foreach ($expiredSales as $sale) {
            // Clean up IN subscription for payment link mode sales
            if ($sale->payment_method === 'invoiceninja' && str_starts_with($sale->transaction_reference, 'sub:')) {
                try {
                    $user = $sale->event->user;
                    $subscriptionId = str_replace('sub:', '', $sale->transaction_reference);
                    $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
                    $invoiceNinja->deleteSubscription($subscriptionId);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete IN subscription during ticket release', [
                        'sale_id' => $sale->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \DB::transaction(function () use ($sale) {
                $sale->status = 'expired';
                $sale->save();
            });
        }
    }
}

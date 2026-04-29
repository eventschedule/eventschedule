<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Services\AuditService;
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
        $expiredSales = Sale::where('status', 'unpaid')
            ->where(function ($q) {
                $q->whereNull('group_id')->orWhereColumn('group_id', 'id');
            })
            ->whereHas('event', function ($query) {
                $query->where('events.expire_unpaid_tickets', '>', 0)
                    ->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) >= events.expire_unpaid_tickets');
            })
            ->get();

        foreach ($expiredSales as $sale) {
            \DB::transaction(function () use ($sale) {
                $sale->status = 'expired';
                $sale->save();
            });

            AuditService::log(AuditService::SALE_EXPIRED, null, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'], 'auto_expire:event_id:'.$sale->event_id);
        }
    }
}

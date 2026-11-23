<?php

namespace App\Console\Commands;

use App\Models\VendorMap;
use App\Services\VendorMaps\VendorClaimNotifier;
use Illuminate\Console\Command;

/**
 * The vendor map's mail (VendorClaimNotifier): the market's editors are told who asked for a
 * spot and who is not coming, at most once a quarter of an hour per map; vendors are told the
 * organizer's answer a couple of minutes after it was given, so the organizer's Undo is real.
 *
 * Every five minutes on both cron rails (routes/console.php and AppController::translateData()).
 * The two can run at once; the notifier's per-map lock keeps a row from being mailed twice.
 */
class SendVendorMapNotices extends Command
{
    protected $signature = 'app:send-vendor-map-notices';

    protected $description = 'Mail vendor map claims to organizers and the organizers\' answers to vendors';

    public function handle(VendorClaimNotifier $notifier): int
    {
        $owners = 0;
        $vendors = 0;

        foreach (VendorClaimNotifier::mapsWithWork()->chunk(100) as $ids) {
            foreach (VendorMap::with('role', 'event')->whereIn('id', $ids->all())->get() as $map) {
                try {
                    $result = $notifier->run($map);
                    $owners += $result['owners'] ? 1 : 0;
                    $vendors += $result['vendors'];
                } catch (\Throwable $e) {
                    // One map's trouble must not cost every map after it its mail.
                    report($e);
                }
            }
        }

        $this->info("Told the organizers of {$owners} vendor maps and answered {$vendors} vendor requests.");

        return Command::SUCCESS;
    }
}

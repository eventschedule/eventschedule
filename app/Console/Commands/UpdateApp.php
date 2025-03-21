<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan;
class UpdateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(UpdaterManager $updater)
    {
        if (config('app.hosted')) {
            $this->error('Not authorized');
            exit;
        }


        $this->info('Updating app, this can take a few minutes...');

        $versionAvailable = $updater->source()->getVersionAvailable();
        $installedVersion = $updater->source()->getVersionInstalled();
        
        cache()->put('version_available', $versionAvailable, 3600);

        if ($versionAvailable == $installedVersion) {
            $this->info('No updates available');
            exit;
        }

        $release = $updater->source()->fetch($versionAvailable);
        
        $updater->source()->update($release);    
        
        Artisan::call('migrate', ['--force' => true]);

        $this->info('App updated successfully! ' . $versionAvailable);
    }
}

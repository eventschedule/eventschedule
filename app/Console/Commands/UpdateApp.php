<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Codedge\Updater\UpdaterManager;
class UpdateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-app';

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
        if (config('app.hosted')) {
            $this->error('Not authorized');
            exit;
        }


        $this->info('Updating app...');

        $updater = new UpdaterManager();

        $versionAvailable = $updater->source()->getVersionAvailable();

        $release = $updater->source()->fetch($versionAvailable);
        
        $updater->source()->update($release);          

        $this->info('App updated successfully! ' . $versionAvailable);
    }
}

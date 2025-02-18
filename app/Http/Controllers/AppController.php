<?php

namespace App\Http\Controllers;

use Codedge\Updater\UpdaterManager;

class AppController extends Controller
{
    public function update(UpdaterManager $updater)
    {
        if($updater->source()->isNewVersionAvailable()) {

            // Get the current installed version
            echo $updater->source()->getVersionInstalled();
    
            // Get the new version available
            $versionAvailable = $updater->source()->getVersionAvailable();
    
            // Create a release
            $release = $updater->source()->fetch($versionAvailable);
    
            // Run the update process
            $updater->source()->update($release);
    
        } else {
            echo "No new version available.";
        }
    }
}
<?php

namespace App\Http\Controllers;

use Codedge\Updater\UpdaterManager;

class AppController extends Controller
{
    public function update(UpdaterManager $updater)
    {
        if (config('app.hosted')) {
            return redirect()->back()->with('error', 'Not authorized');
        }

        if ($updater->source()->isNewVersionAvailable()) {
            $versionAvailable = $updater->source()->getVersionAvailable();
            $release = $updater->source()->fetch($versionAvailable);
            $updater->source()->update($release);    
        } else {
            echo "No new version available.";
        }
    }

    public function setup()
    {
        return view('setup');
    }
}
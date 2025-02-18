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

        try {
            if ($updater->source()->isNewVersionAvailable()) {
                $versionAvailable = $updater->source()->getVersionAvailable();
                $release = $updater->source()->fetch($versionAvailable);
                $updater->source()->update($release);    
            } else {
                echo "No new version available.";
            }                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . $versionAvailable . '/eventschedule.zip" class="hover:underline">eventschedule.zip</a>']));
        }
    }

    public function setup()
    {
        return view('setup');
    }
}
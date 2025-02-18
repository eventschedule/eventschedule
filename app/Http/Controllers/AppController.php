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
                return redirect()->back()->with('error', __('messages.no_new_version_available'));
            }                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('message', __('messages.app_updated'));
    }

    public function setup()
    {
        return view('setup');
    }
}
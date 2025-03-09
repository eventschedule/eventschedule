<?php

namespace App\Http\Controllers;

use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
                
                Artisan::call('migrate', ['--force' => true]);
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

    public function testDatabase(Request $request)
    {
        $host = $request->input('host');
        $port = $request->input('port');
        $database = $request->input('database');
        $username = $request->input('username');
        $password = $request->input('password');

        try {
            $connection = @mysqli_connect($host, $username, $password, $database, (int)$port);
            if (!$connection) {
                throw new \Exception(mysqli_connect_error());
            }
            mysqli_close($connection);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    public function translateData()
    {
        return;
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');
        
        if (! $serverSecret || $requestSecret != $serverSecret) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:translate-data');

        return response()->json(['success' => true]);        
    }
}

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
            return redirect()->to(route('profile.edit') . '#section-app')->with('error', 'Not authorized');
        }

        try {
            if ($updater->source()->isNewVersionAvailable()) {
                $versionAvailable = $updater->source()->getVersionAvailable();
                
                $release = $updater->source()->fetch($versionAvailable);
                
                $updater->source()->update($release);   
                
                Artisan::call('migrate', ['--force' => true]);
            } else {
                return redirect()->to(route('profile.edit') . '#section-app')->with('error', __('messages.no_new_version_available'));
            }                
        } catch (\Exception $e) {
            return redirect()->to(route('profile.edit') . '#section-app')->with('error', $e->getMessage());
        }

        return redirect()->to(route('profile.edit') . '#section-app')->with('message', __('messages.app_updated'));
    }

    public function setup()
    {
        return view('setup');
    }

    public function testDatabase(Request $request)
    {
        // This endpoint is only available for self-hosted setups (not on hosted platform)
        // and should only be used during initial configuration
        if (config('app.hosted')) {
            return response()->json(['success' => false, 'error' => 'Not available'], 403);
        }

        $host = $request->input('host');
        $port = $request->input('port');
        $database = $request->input('database');
        $username = $request->input('username');
        $password = $request->input('password');

        try {
            $connection = @mysqli_connect($host, $username, $password, $database, (int)$port);
            if (!$connection) {
                // Don't expose detailed MySQL errors - they can reveal server information
                \Log::warning('Database connection test failed', ['host' => $host, 'error' => mysqli_connect_error()]);
                return response()->json(['success' => false, 'error' => 'Unable to connect to database. Please check your credentials.']);
            }
            mysqli_close($connection);
        } catch (\Exception $e) {
            \Log::warning('Database connection test exception', ['host' => $host, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Unable to connect to database. Please check your credentials.']);
        }

        return response()->json(['success' => true]);
    }

    public function translateData()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');
        
        if (!$serverSecret || !$requestSecret || !hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:translate');
        \Artisan::call('google:refresh-webhooks');

        $currentHourUtc = (int) now('UTC')->format('H');
        if ($currentHourUtc === 12) {
            \Artisan::call('app:notify-request-changes');
        }

        if (! config('app.hosted')) {
            \Artisan::call('app:import-curator-events');
        }

        return response()->json(['success' => true]);
    }
}

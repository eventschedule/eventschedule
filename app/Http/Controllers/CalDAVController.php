<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\CalDAVService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalDAVController extends Controller
{
    protected $calDAVService;

    public function __construct(CalDAVService $calDAVService)
    {
        $this->calDAVService = $calDAVService;
    }

    /**
     * Test connection to CalDAV server
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'server_url' => ['required', 'url', 'regex:/^https:\/\//i'],
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'server_url.regex' => __('messages.caldav_https_required'),
        ]);

        $settings = [
            'server_url' => $request->server_url,
            'username' => $request->username,
            'password' => $request->password,
        ];

        $result = $this->calDAVService->testConnection($settings);

        return response()->json($result);
    }

    /**
     * Discover calendars on the CalDAV server
     */
    public function discoverCalendars(Request $request)
    {
        $request->validate([
            'server_url' => ['required', 'url', 'regex:/^https:\/\//i'],
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'server_url.regex' => __('messages.caldav_https_required'),
        ]);

        $settings = [
            'server_url' => $request->server_url,
            'username' => $request->username,
            'password' => $request->password,
        ];

        $result = $this->calDAVService->discoverCalendars($settings);

        return response()->json($result);
    }

    /**
     * Save CalDAV settings for a role
     */
    public function saveSettings(Request $request, string $subdomain)
    {
        // Combine subdomain and authorization checks to prevent subdomain enumeration
        $role = Role::where('subdomain', $subdomain)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'server_url' => ['required', 'url', 'regex:/^https:\/\//i'],
            'username' => 'required|string',
            'password' => 'required|string',
            'calendar_url' => ['required', 'url', 'regex:/^https:\/\//i'],
            'sync_direction' => 'nullable|in:to,from,both',
        ], [
            'server_url.regex' => __('messages.caldav_https_required'),
            'calendar_url.regex' => __('messages.caldav_https_required'),
        ]);

        // Test connection before saving
        $testSettings = [
            'server_url' => $request->server_url,
            'username' => $request->username,
            'password' => $request->password,
        ];

        $testResult = $this->calDAVService->testConnection($testSettings);

        if (! $testResult['success']) {
            return response()->json([
                'success' => false,
                'message' => __('messages.connection_failed').': '.$testResult['message'],
            ], 422);
        }

        // Save settings
        $role->setCalDAVSettings([
            'server_url' => $request->server_url,
            'username' => $request->username,
            'password' => $request->password,
            'calendar_url' => $request->calendar_url,
        ]);

        $role->caldav_sync_direction = $request->sync_direction;
        $role->save();

        Log::info('CalDAV settings saved', [
            'role_id' => $role->id,
            'subdomain' => $subdomain,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.caldav_settings_saved'),
        ]);
    }

    /**
     * Disconnect CalDAV from a role
     */
    public function disconnect(string $subdomain)
    {
        // Combine subdomain and authorization checks to prevent subdomain enumeration
        $role = Role::where('subdomain', $subdomain)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $role->caldav_settings = null;
        $role->caldav_sync_direction = null;
        $role->caldav_sync_token = null;
        $role->caldav_last_sync_at = null;
        $role->save();

        Log::info('CalDAV disconnected', [
            'role_id' => $role->id,
            'subdomain' => $subdomain,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.caldav_disconnected'),
        ]);
    }

    /**
     * Update sync direction for a role
     */
    public function updateSyncDirection(Request $request, string $subdomain)
    {
        // Combine subdomain and authorization checks to prevent subdomain enumeration
        $role = Role::where('subdomain', $subdomain)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $role->hasCalDAVSettings()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.caldav_not_configured'),
            ], 422);
        }

        $request->validate([
            'sync_direction' => 'nullable|in:to,from,both',
        ]);

        $role->caldav_sync_direction = $request->sync_direction;
        $role->save();

        Log::info('CalDAV sync direction updated', [
            'role_id' => $role->id,
            'subdomain' => $subdomain,
            'sync_direction' => $request->sync_direction,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.sync_direction_updated'),
        ]);
    }

    /**
     * Manually trigger a sync for a role
     */
    public function sync(Request $request, string $subdomain)
    {
        // Combine subdomain and authorization checks to prevent subdomain enumeration
        $role = Role::where('subdomain', $subdomain)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $role->hasCalDAVSettings()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.caldav_not_configured'),
            ], 422);
        }

        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        // Sync to CalDAV
        if ($role->syncsToCalDAV()) {
            $toResults = $this->calDAVService->syncToCalDAV($role);
            $results['created'] += $toResults['created'];
            $results['updated'] += $toResults['updated'];
            $results['errors'] += $toResults['errors'];
        }

        // Sync from CalDAV
        if ($role->syncsFromCalDAV()) {
            $fromResults = $this->calDAVService->syncFromCalDAV($role);
            $results['created'] += $fromResults['created'];
            $results['updated'] += $fromResults['updated'];
            $results['errors'] += $fromResults['errors'];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => __('messages.sync_complete'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\OneSignalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages a user's device-level push subscription state and the "send test
 * push" action. The actual browser subscription lives in OneSignal; here we
 * persist whether the user has opted into push delivery (users.push_settings).
 */
class PushController extends Controller
{
    /**
     * Record that the user has enabled push on a device. Called by the client
     * after the browser grants permission and OneSignal opts the device in.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->push_settings ?? [];
        $settings['enabled'] = true;
        $settings['opted_in_at'] = now()->toIso8601String();
        $user->push_settings = $settings;
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Stop routing push notifications to the user (server-side flag; the
     * browser subscription is opted out client-side separately).
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->push_settings ?? [];
        $settings['enabled'] = false;
        $user->push_settings = $settings;
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Send a test push to the current user's subscribed devices.
     */
    public function test(Request $request): JsonResponse
    {
        if (! OneSignalService::isConfigured()) {
            return response()->json(['error' => __('messages.push_not_configured')], 422);
        }

        OneSignalService::pushToUser($request->user(), [
            'title_key' => 'messages.push_send_test',
            'body_key' => 'messages.push_test_sent',
            'url' => route('profile.edit'),
        ], null, true);

        return response()->json(['success' => true, 'message' => __('messages.push_test_sent')]);
    }
}

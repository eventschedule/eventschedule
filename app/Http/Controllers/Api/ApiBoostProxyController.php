<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetaAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiBoostProxyController extends Controller
{
    protected array $allowedActions = [
        'create_campaign',
        'upload_image',
        'pause_campaign',
        'resume_campaign',
        'delete_campaign',
        'get_insights',
        'get_ad_insights',
        'check_status',
        'search_interests',
        'estimate_reach',
    ];

    public function handle(Request $request, MetaAdsService $metaService): JsonResponse
    {
        // Validate API key
        $apiKey = config('services.meta.proxy_api_key');
        if (empty($apiKey) || ! hash_equals($apiKey, (string) $request->header('X-API-Key', ''))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Rate limit by IP
        $rateKey = 'boost_proxy_rate:'.$request->ip();
        $attempts = Cache::get($rateKey, 0);
        if ($attempts >= 60) {
            return response()->json(['error' => 'Too many requests'], 429);
        }
        Cache::put($rateKey, $attempts + 1, now()->addMinute());

        // Validate request
        $action = $request->input('action');
        $data = $request->input('data');

        if (! $action || ! in_array($action, $this->allowedActions)) {
            return response()->json(['error' => 'Invalid action'], 422);
        }

        if (! is_array($data)) {
            return response()->json(['error' => 'Data must be an array'], 422);
        }

        // Check Meta is configured on this server
        if (! $metaService->isConfigured()) {
            return response()->json(['error' => 'Meta Ads not configured on proxy server'], 500);
        }

        try {
            $result = match ($action) {
                'create_campaign' => $metaService->proxyCreateCampaign($data),
                'upload_image' => $metaService->proxyUploadImage($data),
                'pause_campaign' => $metaService->proxyPauseCampaign($data),
                'resume_campaign' => $metaService->proxyResumeCampaign($data),
                'delete_campaign' => $metaService->proxyDeleteCampaign($data),
                'get_insights' => $metaService->proxyGetInsights($data),
                'get_ad_insights' => $metaService->proxyGetAdInsights($data),
                'check_status' => $metaService->proxyCheckStatus($data),
                'search_interests' => $metaService->proxySearchInterests($data),
                'estimate_reach' => $metaService->proxyEstimateReach($data),
            };

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Boost proxy error', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

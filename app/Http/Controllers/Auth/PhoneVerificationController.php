<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PhoneVerificationController extends Controller
{
    /**
     * Send a verification code to the user's phone number.
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
        ]);

        $phone = $request->phone;

        if ($phone !== $request->user()->phone) {
            return response()->json([
                'success' => false,
                'message' => __('messages.phone_verification_code_invalid'),
            ], 422);
        }

        // Rate limiting: max 5 codes per hour per phone
        $attemptsKey = 'phone_verify_attempts_'.$phone;
        $attempts = Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => __('messages.code_rate_limit'),
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store mapping (phone -> code) for validation
        Cache::put('phone_verify_code_'.$phone, $code, now()->addMinutes(10));

        // Increment attempts counter
        Cache::put($attemptsKey, $attempts + 1, now()->addHour());

        // Send SMS
        $sent = SmsService::sendSms($phone, __('messages.your_verification_code_is', ['code' => $code]));

        if (! $sent) {
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_send_sms'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.verification_code_sent_to_phone'),
        ]);
    }

    /**
     * Verify the code and mark the user's phone as verified.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $storedCode = Cache::pull('phone_verify_code_'.$request->user()->phone);

        if (! $storedCode || ! hash_equals($storedCode, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.phone_verification_code_invalid'),
            ], 422);
        }

        $user = $request->user();
        $user->phone_verified_at = now();
        $user->save();

        // Auto-claim unclaimed roles with matching phone
        $roles = Role::where('phone', $user->phone)
            ->whereNull('user_id')
            ->get();

        foreach ($roles as $role) {
            $role->user_id = $user->id;
            $role->phone_verified_at = now();
            $role->save();

            $user->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.phone_verified'),
        ]);
    }
}

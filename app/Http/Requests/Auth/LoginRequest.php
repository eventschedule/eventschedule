<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidTurnstile;
use App\Services\AuditService;
use App\Utils\StubAccountUtils;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * How many passwordless-account hints one IP may be given per minute.
     *
     * Matches /reset-password's own route throttle, which is the door this branch mirrors. See
     * passwordlessAccountMessage() for why the route's throttle:30,1 is not a substitute.
     */
    private const STUB_HINT_PER_IP = 5;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Before the stub branch on purpose: this IS a failed login and belongs in the record
            // as one, whatever we go on to tell the person.
            AuditService::log(AuditService::AUTH_LOGIN_FAILED, null, null, null, null, null, $this->string('email'));

            throw ValidationException::withMessages([
                'email' => $this->passwordlessAccountMessage(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * What to say about a failed attempt, once we know whether the account has a password at all.
     *
     * A stub is a dead end at this form and always was: validateCredentials() returns false on a
     * null hash, so "these credentials do not match" was the only thing we could say, and it is not
     * what happened. This is the door people actually try after subscribing to a newsletter.
     *
     * Not gated on public_registration_enabled(). That matters most on a selfhost install with
     * registration closed, where RegisteredUserController::create() and store() both bounce to
     * /login before ever reaching their stub-upgrade branch - so for an invited admin this door and
     * the reset link are the ONLY way in, and gating them would lock them out of their own install.
     *
     * Yes, this confirms the address exists. Accepted deliberately: /sign_up already answers
     * "email_already_registered" for a real account, so the app enumerates there today, and the
     * alternative is leaving somebody staring at a message that is untrue.
     *
     * Accepting the disclosure is not the same as accepting it at any RATE, hence the per-IP ceiling
     * below. This route carries throttle:30,1 while /reset-password - the door this mirrors - carries
     * throttle:5,1, and ensureIsNotRateLimited()'s own bucket is keyed on email|ip, so it bounds
     * repeat attempts against ONE address and does nothing about breadth. Without this, one IP could
     * probe and mail thirty distinct addresses a minute here against five there. Turnstile is not a
     * backstop: ValidTurnstile passes on custom domains, under testing, and on any selfhost with no
     * keys configured.
     */
    private function passwordlessAccountMessage(): string
    {
        $stub = StubAccountUtils::find($this->input('email'));

        if (! $stub) {
            return trans('auth.failed');
        }

        // Spent on the DISCLOSURE, not just on a mail, because knowing which addresses are
        // passwordless is the half an attacker gets even when every send is throttled.
        $ipKey = 'stub-hint-ip:'.$this->ip();

        if (RateLimiter::tooManyAttempts($ipKey, self::STUB_HINT_PER_IP)) {
            return trans('auth.failed');
        }

        RateLimiter::hit($ipKey, 60);

        return match (StubAccountUtils::send($stub)) {
            StubAccountUtils::SENT => __('messages.login_no_password_yet'),
            // Never claims a fresh send - see StubAccountUtils::send() for the two throttles.
            StubAccountUtils::THROTTLED => __('messages.login_password_link_already_sent'),
            default => trans('auth.failed'),
        };
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}

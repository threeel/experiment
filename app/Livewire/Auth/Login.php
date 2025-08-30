<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public ?string $code = null;

    public bool $otpSent = false;

    public int $resendIn = 0;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        // If user has OTP enabled, use OTP flow instead of password
        $userClass = get_class(auth()->getProvider()->createModel());
        /** @var \Illuminate\Database\Eloquent\Model|null $otpUser */
        $otpUser = $userClass::query()->where('email', $this->email)->first();
        if ($otpUser && property_exists($otpUser, 'otp_enabled') ? $otpUser->otp_enabled : (bool) ($otpUser->otp_enabled ?? false)) {
            if (! filled($this->password)) {
                $this->loginWithOtp();

                return;
            }
        }

        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // If Fortify two-factor is enabled (and OTP is disabled) and user has it confirmed, redirect to the challenge
        if ($user && $user->two_factor_secret && $user->two_factor_confirmed_at && ! (bool) ($user->otp_enabled ?? false)) {
            Auth::logout();

            // Save login state for Fortify two-factor challenge
            Session::put('login.id', $user->getAuthIdentifier());
            Session::put('login.remember', $this->remember);

            RateLimiter::clear($this->throttleKey());
            Session::regenerate();

            $this->redirectRoute('two-factor.login', navigate: true);

            return;
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function loginWithOtp(): void
    {
        // First step: send code if not sent yet
        if (! $this->otpSent) {
            $this->validateOnly('email');
            $this->ensureIsNotRateLimited();

            // Do not reveal if user exists; only send if exists
            $userClass = get_class(auth()->getProvider()->createModel());
            /** @var \Illuminate\Database\Eloquent\Model|null $user */
            $user = $userClass::query()->where('email', $this->email)->first();

            if ($user) {
                $service = app(\App\Services\Otp\OtpService::class);
                if ($service->canResend($this->email)) {
                    $code = $service->generate($this->email);
                    $service->send($this->email, $code);
                    $this->resendIn = (int) config('otp.throttle_seconds', 30);
                } else {
                    $this->resendIn = $this->getResendAvailableInSeconds();
                }
            }

            // Always act successful to avoid enumeration
            $this->otpSent = true;
            session()->flash('status', __('If an account exists for that email, we have sent a login code.'));

            return;
        }

        // Second step: verify code
        $this->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|min:'.(int) config('otp.code_length', 6).'|max:'.(int) config('otp.code_length', 6),
        ]);

        $service = app(\App\Services\Otp\OtpService::class);
        if (! $service->verify($this->email, (string) $this->code)) {
            throw ValidationException::withMessages([
                'code' => __('The provided code is invalid or expired.'),
            ]);
        }

        // Locate user and log in
        $userClass = get_class(auth()->getProvider()->createModel());
        $user = $userClass::query()->where('email', $this->email)->first();
        if (! $user) {
            // Fail silently to avoid enumeration
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function tick(): void
    {
        if ($this->resendIn > 0) {
            $this->resendIn--;
        }
    }

    public function resendCode(): void
    {
        // Only act if otp flow active
        $userClass = get_class(auth()->getProvider()->createModel());
        $otpUser = $userClass::query()->where('email', $this->email)->first();
        if (! ($otpUser && (bool) ($otpUser->otp_enabled ?? false)) || ! $this->otpSent) {
            return;
        }

        $this->validateOnly('email');

        $service = app(\App\Services\Otp\OtpService::class);

        if (! $service->canResend($this->email)) {
            $this->resendIn = $this->getResendAvailableInSeconds();

            return;
        }

        // Enumeration safe: only send if user exists
        $userClass = get_class(auth()->getProvider()->createModel());
        $user = $userClass::query()->where('email', $this->email)->first();
        if ($user) {
            $code = $service->generate($this->email);
            $service->send($this->email, $code);
        }

        $this->resendIn = (int) config('otp.throttle_seconds', 30);
        session()->flash('status', __('If an account exists for that email, we have sent a new login code.'));
    }

    protected function getResendAvailableInSeconds(): int
    {
        // Approximate remaining time by trying to generate and falling back? We can’t from here.
        // Since OtpService stores throttle in cache with a TTL, we cannot read it directly without a method.
        // For simplicity, keep previous countdown or set to a default throttle.
        return max(1, $this->resendIn);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Component;

class TwoFactor extends Component
{
    public string $code = '';

    public bool $showingRecoveryCodes = false;

    public function mount(): void
    {
        if (config('otp.enabled')) {
            abort(404);
        }
    }

    public function enableTwoFactorAuthentication(TwoFactorAuthenticationProvider $provider): void
    {
        /** @var Authenticatable&\Laravel\Fortify\TwoFactorAuthenticatable $user */
        $user = Auth::user();

        if ($user->two_factor_secret) {
            return; // already enabled
        }

        $secret = $provider->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->dispatch('two-factor-enabled');
    }

    public function confirmTwoFactorAuthentication(TwoFactorAuthenticationProvider $provider): void
    {
        /** @var Authenticatable&\Laravel\Fortify\TwoFactorAuthenticatable $user */
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            return;
        }

        if ($provider->verify((string) decrypt($user->two_factor_secret), $this->code)) {
            $user->forceFill([
                'two_factor_confirmed_at' => Date::now(),
            ])->save();

            $this->reset('code');
            $this->dispatch('two-factor-confirmed');
        } else {
            $this->addError('code', __('The provided two factor authentication code was invalid.'));
        }
    }

    public function regenerateRecoveryCodes(): void
    {
        /** @var Authenticatable&\Laravel\Fortify\TwoFactorAuthenticatable $user */
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            return;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ])->save();

        $this->showingRecoveryCodes = true;
        $this->dispatch('recovery-codes-regenerated');
    }

    public function disableTwoFactorAuthentication(): void
    {
        /** @var Authenticatable&\Laravel\Fortify\TwoFactorAuthenticatable $user */
        $user = Auth::user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->reset('code', 'showingRecoveryCodes');
        $this->dispatch('two-factor-disabled');
    }

    /**
     * @return array<int, string>
     */
    protected function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::random(10).'-'.Str::random(10))
            ->all();
    }
}

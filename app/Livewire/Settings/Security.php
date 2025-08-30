<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Security extends Component
{
    public bool $otpEnabled = false;

    #[Validate('nullable|string')]
    public ?string $otpDriver = null;

    public array $availableDrivers = [];

    public function mount(): void
    {
        /** @var Authenticatable&\App\Models\User $user */
        $user = Auth::user();

        $this->otpEnabled = (bool) $user->otp_enabled;
        $this->otpDriver = $user->otp_driver ?? config('otp.default');
        $this->availableDrivers = array_keys((array) config('otp.drivers', []));
    }

    public function updatedOtpEnabled(bool $value): void
    {
        if ($value) {
            // Enabling OTP requires a driver and disabling Two-Factor first
            $this->enableOtp();
        } else {
            $this->disableOtp();
        }
    }

    public function enableOtp(): void
    {
        $this->validate([
            'otpDriver' => 'required|string|in:'.implode(',', $this->availableDrivers),
        ], [
            'otpDriver.required' => __('Please select a driver to enable OTP.'),
        ]);

        /** @var Authenticatable&\Laravel\Fortify\TwoFactorAuthenticatable $user */
        $user = Auth::user();

        // Ensure Two-Factor is disabled before enabling OTP
        if ($user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();
        }

        $user->forceFill([
            'otp_enabled' => true,
            'otp_driver' => $this->otpDriver,
        ])->save();

        $this->otpEnabled = true;
        $this->dispatch('otp-enabled');
    }

    public function disableOtp(): void
    {
        /** @var Authenticatable $user */
        $user = Auth::user();

        $user->forceFill([
            'otp_enabled' => false,
        ])->save();

        $this->otpEnabled = false;
        $this->dispatch('otp-disabled');
    }

    public function switchOtpDriver(): void
    {
        // Switching driver must also ensure Two-Factor is disabled
        $this->enableOtp();
    }

    public function render()
    {
        return view('livewire.settings.security');
    }
}

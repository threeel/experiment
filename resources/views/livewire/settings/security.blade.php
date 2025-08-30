<x-settings.layout :heading="__('Security')" :subheading="__('Manage Two-Factor Authentication and One-Time Password (OTP) login')">
    <div class="space-y-8">
        <div>
            <flux:heading size="lg">{{ __('One-Time Password (OTP)') }}</flux:heading>
            <div class="mt-3 space-y-3">
                <flux:field>
                    <flux:label>{{ __('Enable OTP login') }}</flux:label>
                    <div class="flex items-center gap-3">
                        <flux:switch wire:model="otpEnabled" />
                        <span class="text-sm opacity-70">{{ __('Use a login code sent via the selected method.') }}</span>
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('OTP Method') }}</flux:label>
                    <flux:select wire:model="otpDriver" :disabled="! $otpEnabled">
                        <option value="" disabled>{{ __('Select an authentication method') }}</option>
                        @foreach ($availableDrivers as $driver)
                            <option value="{{ $driver }}">{{ ucfirst($driver) }}</option>
                        @endforeach
                    </flux:select>
                    @error('otpDriver')
                        <flux:subheading class="text-red-600 dark:text-red-400 mt-2">{{ $message }}</flux:subheading>
                    @enderror
                </flux:field>

                <div class="flex gap-2">
                    <flux:button wire:click="enableOtp" :disabled="! $otpDriver">{{ __('Enable / Update OTP') }}</flux:button>
                    <flux:button variant="danger" wire:click="disableOtp" :disabled="! $otpEnabled">{{ __('Disable OTP') }}</flux:button>
                </div>

                <flux:callout class="mt-2" variant="warning">
                    {{ __('Enabling or switching OTP will disable Two-Factor Authentication automatically.') }}
                </flux:callout>
            </div>
        </div>

        <flux:separator />

        <div>
            <flux:heading size="lg">{{ __('Two-Factor Authentication') }}</flux:heading>
            <flux:subheading class="opacity-70 mt-1">{{ __('Use an authenticator app to secure your account.') }}</flux:subheading>

            @if (auth()->user()->otp_enabled)
                <flux:callout class="mt-3" variant="warning">
                    {{ __('OTP is currently enabled. To use Two-Factor, disable OTP above first.') }}
                </flux:callout>
            @else
                <livewire:settings.two-factor />
            @endif
        </div>
    </div>
</x-settings.layout>

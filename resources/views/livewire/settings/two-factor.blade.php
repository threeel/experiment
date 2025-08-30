<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Two-Factor Authentication')" :subheading="__('Add additional security to your account using two factor authentication')">
        @php($user = auth()->user())

        @if (! $user->two_factor_secret)
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your authenticator application such as Google Authenticator or 1Password.') }}
                </p>

                <flux:button wire:click="enableTwoFactorAuthentication" variant="primary">
                    {{ __('Enable Two-Factor') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-6">
                @if (! $user->two_factor_confirmed_at)
                    <div class="rounded-lg border p-4">
                        <flux:heading size="sm">{{ __('Finish enabling two-factor authentication') }}</flux:heading>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Scan the following QR code using your authenticator app, or enter the setup key, then provide the OTP to confirm.') }}
                        </p>

                        <div class="mt-4 flex flex-col items-center gap-4">
                            <div class="bg-white p-2 dark:bg-white">
                                {!! $user->twoFactorQrCodeSvg() !!}
                            </div>
                            <div class="text-xs text-gray-700">
                                <span class="font-medium">{{ __('Setup Key:') }}</span>
                                <span class="select-all">{{ decrypt($user->two_factor_secret) }}</span>
                            </div>
                        </div>

                        <form class="mt-4 space-y-3" wire:submit="confirmTwoFactorAuthentication">
                            <flux:input
                                wire:model="code"
                                :label="__('One-time password')"
                                placeholder="123456"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                required
                            />

                            <div class="flex items-center gap-4">
                                <flux:button type="submit" variant="primary">{{ __('Confirm') }}</flux:button>
                                <x-action-message class="me-3" on="two-factor-confirmed">{{ __('Confirmed.') }}</x-action-message>
                            </div>
                        </form>
                    </div>
                @else
                    <flux:callout variant="success">{{ __('Two-factor authentication is enabled and confirmed.') }}</flux:callout>
                @endif

                <div class="rounded-lg border p-4">
                    <flux:heading size="sm">{{ __('Recovery Codes') }}</flux:heading>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.') }}
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($user->recoveryCodes() as $recoveryCode)
                            <code class="rounded bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100">{{ $recoveryCode }}</code>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <flux:button wire:click="regenerateRecoveryCodes">{{ __('Regenerate Recovery Codes') }}</flux:button>
                        <x-action-message class="me-3" on="recovery-codes-regenerated">{{ __('Done.') }}</x-action-message>
                    </div>
                </div>

                <div class="border-t" />

                <div class="mt-2">
                    <flux:button wire:click="disableTwoFactorAuthentication" variant="danger">{{ __('Disable Two-Factor') }}</flux:button>
                    <x-action-message class="ms-3" on="two-factor-disabled">{{ __('Disabled.') }}</x-action-message>
                </div>
            </div>
        @endif
    </x-settings.layout>
</section>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="config('otp.enabled') ? __('Enter your email to receive a login code') : __('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        @if (! config('otp.enabled'))
            <!-- Password -->
            <div class="relative">
                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>
        @else
            @if ($this->otpSent)
                <div class="flex flex-col gap-3" wire:poll.1s="tick">
                    <flux:input
                        wire:model="code"
                        :label="__('One-Time Code')"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        required
                        :placeholder="str_repeat('•', (int) config('otp.code_length', 6))"
                    />

                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <div>
                            @if ($this->resendIn > 0)
                                {{ __('You can resend a new code in :seconds seconds.', ['seconds' => $this->resendIn]) }}
                            @else
                                {{ __('Didn\'t receive a code?') }}
                            @endif
                        </div>
                        <div>
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="resendCode"
                                :disabled="$this->resendIn > 0"
                            >
                                {{ __('Resend code') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                @if (config('otp.enabled'))
                    {{ $this->otpSent ? __('Verify Code') : __('Send Code') }}
                @else
                    {{ __('Log in') }}
                @endif
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>

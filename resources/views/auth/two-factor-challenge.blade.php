<x-layouts.guest>
    <div class="mx-auto w-full max-w-md">
        <flux:heading class="mb-2">{{ __('Two-Factor Authentication') }}</flux:heading>
        <flux:subheading class="mb-6">{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}</flux:subheading>

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>{{ __('Code') }}</flux:label>
                <flux:input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus />
                <flux:error name="code" />
            </flux:field>

            <div class="mt-6">
                <flux:button type="submit" variant="primary" class="w-full">{{ __('Log in') }}</flux:button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-300">
            <span>{{ __('Can\'t access your authenticator?') }}</span>
            <flux:link :href="route('two-factor.login.recovery')">{{ __('Use Recovery Code instead') }}</flux:link>
        </div>
    </div>
</x-layouts.guest>

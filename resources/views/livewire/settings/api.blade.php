<x-settings.layout :heading="__('API Access')" :subheading="__('Create and manage personal access tokens for programmatic access')">
    <div>
        <flux:field>
            <flux:label>{{ __('Token name') }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="tokenName" placeholder="e.g. CI Script" />
            @error('tokenName')
                <flux:subheading class="text-red-600 dark:text-red-400 mt-2">{{ $message }}</flux:subheading>
            @enderror
        </flux:field>

        <div class="mt-4 flex gap-3">
            <flux:button wire:click="createToken" :disabled="! $tokenName">{{ __('Create Token') }}</flux:button>
        </div>

        @if ($plainTextToken)
            <flux:callout class="mt-4" variant="success">
                <div class="space-y-2">
                    <div class="font-medium">{{ __('Personal Access Token Created') }}</div>
                    <div class="text-sm opacity-80">{{ __('This token will only be shown once. Please copy and store it securely.') }}</div>
                    <div class="flex items-stretch gap-2" x-data>
                        <flux:input
                            x-ref="tokenInput"
                            type="text"
                            readonly
                            value="{{ $plainTextToken }}"
                            class="w-full font-mono text-sm"
                            aria-label="{{ __('Your new personal access token') }}"
                        />
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md px-3 min-w-10 text-sm text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-800/5 dark:text-white/80 dark:ring-white/20 dark:hover:bg-white/[7%]"
                            aria-label="{{ __('Copy token to clipboard') }}"
                            x-data="{ async copy() { try { if (navigator.clipboard && window.isSecureContext) { await navigator.clipboard.writeText($refs.tokenInput.value); } else { const el = document.createElement('textarea'); el.value = $refs.tokenInput.value; el.style.position = 'fixed'; el.style.opacity = '0'; document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el); } $dispatch('flux-toast', { title: '{{ __('Copied') }}', description: '{{ __('Token copied to clipboard') }}', variant: 'success' }); } catch (e) { $dispatch('flux-toast', { title: '{{ __('Copy failed') }}', description: '{{ __('Please copy the token manually.') }}', variant: 'danger' }); } } }"
                            @click="copy()"
                            data-testid="copy-token-button"
                        >
                            <flux:icon.clipboard class="size-5" />
                        </button>
                    </div>
                </div>
            </flux:callout>
        @endif

        <flux:separator class="my-6" />

        <flux:heading size="lg">{{ __('Active Tokens') }}</flux:heading>
        <div class="mt-3 space-y-2">
            @forelse (auth()->user()->tokens()->latest()->get() as $token)
                <div class="flex items-center justify-between rounded-lg border border-gray-200/70 dark:border-white/10 p-3">
                    <div>
                        <div class="font-medium">{{ $token->name }}</div>
                        <div class="text-xs opacity-70">{{ __('Created') }} {{ $token->created_at->diffForHumans() }} • {{ __('Last used') }} {{ optional($token->last_used_at)->diffForHumans() ?? __('Never') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($token->expires_at)
                            <span class="text-xs opacity-70">{{ __('Expires') }} {{ $token->expires_at->diffForHumans() }}</span>
                        @endif
                        <flux:button variant="danger" size="sm" wire:click="revokeToken({{ $token->id }})">{{ __('Revoke') }}</flux:button>
                    </div>
                </div>
            @empty
                <flux:subheading class="opacity-70">{{ __('No tokens yet.') }}</flux:subheading>
            @endforelse
        </div>
    </div>
</x-settings.layout>

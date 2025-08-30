<p>{{ __('Hello :name,', ['name' => $user->name]) }}</p>
<p>{{ __('Your account is scheduled to be permanently deleted on :date.', ['date' => $scheduledAt->toDayDateTimeString()]) }}</p>
<p>{{ __('If you want to keep your account, you can stop the deletion process by clicking the link below:') }}</p>
<p><a href="{{ $cancelUrl }}">{{ __('Keep my account') }}</a></p>
<p>{{ __('If you take no action, your account and associated data will be permanently deleted.') }}</p>

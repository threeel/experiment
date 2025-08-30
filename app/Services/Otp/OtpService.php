<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    public int $ttl;

    public int $throttle;

    public int $length;

    public function __construct()
    {
        $this->ttl = (int) config('otp.ttl', 300);
        $this->throttle = (int) config('otp.throttle_seconds', 30);
        $this->length = (int) config('otp.code_length', 6);
    }

    protected function cacheKey(string $email): string
    {
        return 'otp:login:'.Str::lower($email);
    }

    protected function throttleKey(string $email): string
    {
        return 'otp:throttle:'.Str::lower($email);
    }

    public function canResend(string $email): bool
    {
        return ! Cache::has($this->throttleKey($email));
    }

    public function generate(string $email): string
    {
        $code = str_pad((string) random_int(0, (10 ** $this->length) - 1), $this->length, '0', STR_PAD_LEFT);
        Cache::put($this->cacheKey($email), $code, $this->ttl);
        Cache::put($this->throttleKey($email), true, $this->throttle);

        return $code;
    }

    public function verify(string $email, string $code): bool
    {
        $stored = Cache::get($this->cacheKey($email));
        if (! $stored) {
            return false;
        }
        $valid = hash_equals($stored, trim($code));
        if ($valid) {
            Cache::forget($this->cacheKey($email));
        }

        return $valid;
    }

    public function send(string $email, string $code): void
    {
        if (config('otp.default') === 'email') {
            $subject = config('otp.drivers.email.subject', 'Your Login Code');
            $from = config('otp.drivers.email.from');
            $name = config('otp.drivers.email.name');

            Mail::raw("Your one-time login code is: {$code}\nIt expires in ".(int) ($this->ttl / 60).' minutes.', function ($message) use ($email, $subject, $from, $name) {
                $message->to($email)
                    ->subject($subject);
                if ($from) {
                    $message->from($from, $name);
                }
            });
        }
        // Future drivers (e.g., SMS) can be added here.
    }
}

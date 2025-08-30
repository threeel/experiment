<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('uses non-TLS for Reverb in local env when REVERB_SCHEME=http', function () {
    expect(Config::get('broadcasting.connections.reverb.options.scheme'))->toBe('http');
    expect(Config::get('broadcasting.connections.reverb.options.useTLS'))->toBeFalse();
});

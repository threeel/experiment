<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse as TwoFactorChallengeViewResponseContract;

class TwoFactorChallengeViewResponse implements TwoFactorChallengeViewResponseContract
{
    /**
     * Get the response for the two-factor authentication challenge view.
     */
    public function toResponse($request): mixed
    {
        return response()->view('auth.two-factor-challenge');
    }
}

<?php

return [
    'grace_days' => env('ACCOUNT_DELETION_GRACE_DAYS', 30),
    'reminder_days_before' => env('ACCOUNT_DELETION_REMINDER_DAYS_BEFORE', 3),
];

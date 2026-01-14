<?php

declare(strict_types=1);

namespace App\Constants;

class Constant
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public const DEFAULT_PER_PAGE = 15;

    public const PAYOUT_TYPE_BANK = 'bank';

    public const PAYOUT_TYPE_PAYPAL = 'paypal';

    public const PAYOUT_TYPE_CRYPTO = 'crypto';

    public const ACCOUNT_TYPE_SAVINGS = 'savings';

    public const ACCOUNT_TYPE_CHECKING = 'checking';

    public const ACCOUNT_TYPE_OTHER = 'other';
}

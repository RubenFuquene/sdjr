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

    // Document upload status
    public const UPLOAD_STATUS_PENDING = 'pending';
    public const UPLOAD_STATUS_CONFIRMED = 'confirmed';
    public const UPLOAD_STATUS_FAILED = 'failed';
    public const UPLOAD_STATUS_ORPHANED = 'orphaned';

    // Document types (ajustar según catálogo real)
    public const DOCUMENT_TYPE_ID_CARD = 'ID_CARD';
    public const DOCUMENT_TYPE_LICENSE = 'LICENSE';
    public const DOCUMENT_TYPE_OTHER = 'OTHER';
    public const DOCUMENT_TYPE_CAMARA_COMERCIO = 'CAMARA_COMERCIO';
    public const DOCUMENT_TYPE_RUT = 'RUT';
    public const DOCUMENT_TYPE_REGISTRATION = 'REGISTRATION';
    
    // Legal Document Types
    public const LEGAL_DOCUMENT_TYPE_TERMS = 'terms';
    public const LEGAL_DOCUMENT_TYPE_PRIVACY = 'privacy';
    public const LEGAL_DOCUMENT_TYPE_SERVICE_CONTRACT = 'service_contract';

    // Legal Document Status
    public const LEGAL_DOCUMENT_STATUS_DRAFT = 'draft';
    public const LEGAL_DOCUMENT_STATUS_ACTIVE = 'active';
    public const LEGAL_DOCUMENT_STATUS_ARCHIVED = 'archived';
}

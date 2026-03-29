<?php

declare(strict_types=1);

namespace App\Constants;

class Constant
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public const STATUS_REJECTED = 2;

    public const STATUS_PENDING = 3;

    public const DEFAULT_PER_PAGE = 15;

    // Payout methods
    public const PAYOUT_TYPE_BANK = 'bank';

    public const PAYOUT_TYPE_PAYPAL = 'paypal';

    public const PAYOUT_TYPE_CRYPTO = 'crypto';

    // Bank account types
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

    // Allowed file extensions for uploads
    public const ALLOWED_FILE_EXTENSIONS = ['pdf', 'jpg', 'png', 'docx', 'jpeg'];

    public const ALLOWED_SIZE_BYTES = 52428800; // 50 MB

    // Allowed image extension
    public const ALLOWED_PHOTO_EXTENSIONS = ['jpg', 'png', 'jpeg'];

    public const ALLOWED_PHOTO_SIZE_BYTES = 5242880; // 5 MB

    public const MAX_PHOTOS_PER_PRODUCT = 3;

    // Product Types
    public const PRODUCT_TYPE_SINGLE = 'single';

    public const PRODUCT_TYPE_PACKAGE = 'package';

    // Order Status
    public const ORDER_STATUS_PENDING = 'pending';

    public const ORDER_STATUS_CONFIRMED = 'confirmed';

    public const ORDER_STATUS_PREPARING = 'preparing';

    public const ORDER_STATUS_READY = 'ready';

    public const ORDER_STATUS_DELIVERED = 'delivered';

    public const ORDER_STATUS_CANCELLED = 'cancelled';

    // Order validations
    public const MIN_ORDER_QUANTITY = 1;

    public const MAX_ORDER_ITEMS = 50;

    // Commerce Branch Photos
    public const MAX_PHOTOS_PER_COMMERCE_BRANCH = 5;

    public const COMMERCE_PENDING = 0;

    public const COMMERCE_VERIFIED = 1;

    public const COMMERCE_REJECTED = 2;

    // Commerce Document Types
    public const COMMERCE_DOCUMENT_TYPE_NIT = 'NIT';

    public const COMMERCE_DOCUMENT_TYPE_CC = 'CC';

    public const COMMERCE_DOCUMENT_TYPE_PS = 'PS';

    public const COMMERCE_DOCUMENT_TYPE_CE = 'CE';

    // Commerce Comment Priorities
    public const COMMENT_PRIORITY = 'PR';

    public const COMMENT_PRIORITY_HIGH = 'AL';

    public const COMMENT_PRIORITY_MEDIUM = 'ME';

    public const COMMENT_PRIORITY_LOW = 'BA';

    // Commerce Comment Types
    public const COMMENT_TYPE_PRODUCT = 'PR';

    public const COMMENT_TYPE_SUPPORT = 'SU';

    public const COMMENT_TYPE_INFO = 'IN';

    public const COMMENT_TYPE_VALIDATION = 'VA';

    public const COMMENT_TYPE_ARRAY = [
        self::COMMENT_TYPE_PRODUCT => 'Producto',
        self::COMMENT_TYPE_SUPPORT => 'Soporte',
        self::COMMENT_TYPE_INFO => 'Información',
        self::COMMENT_TYPE_VALIDATION => 'Validación',
    ];

    // Nearby search radius (km)
    public const DEFAULT_SEARCH_RADIUS_KM = 10;

    public const MAX_SEARCH_RADIUS_KM = 50;
}

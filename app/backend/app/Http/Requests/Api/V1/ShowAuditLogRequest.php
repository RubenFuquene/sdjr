<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ShowAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.audit_logs.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;


class StoreLegalDocumentRequest extends FormRequest

/**
 * @OA\Schema(
 *     schema="StoreLegalDocumentRequest",
 *     title="Store Legal Document Request",
 *     description="Request body for creating a legal document",
 *     required={"type", "title", "content", "status"},
 *     @OA\Property(property="type", type="string", example="terms", enum={"terms", "privacy", "service_contract"}),
 *     @OA\Property(property="title", type="string", example="Términos y condiciones"),
 *     @OA\Property(property="content", type="string", example="<html>...</html>"),
 *     @OA\Property(property="version", type="string", example="v1.0"),
 *     @OA\Property(property="status", type="string", example="active", enum={"draft", "active", "archived"}),
 *     @OA\Property(property="effective_date", type="string", format="date", example="2026-02-25")
 * )
 */
{
    public function authorize(): bool
    {
        return $this->user()?->can('legal_documents.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.implode(',', [
                Constant::LEGAL_DOCUMENT_TYPE_TERMS,
                Constant::LEGAL_DOCUMENT_TYPE_PRIVACY,
                Constant::LEGAL_DOCUMENT_TYPE_SERVICE_CONTRACT,
            ])],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:'.implode(',', [
                Constant::LEGAL_DOCUMENT_STATUS_DRAFT,
                Constant::LEGAL_DOCUMENT_STATUS_ACTIVE,
                Constant::LEGAL_DOCUMENT_STATUS_ARCHIVED,
            ])],
            'effective_date' => ['nullable', 'date'],
        ];
    }
}

/**
 * Document Upload API Module
 * Presigned URL flow for commerce documents
 */

import { fetchWithErrorHandling } from "./client";

// ============================================
// Types
// ============================================

export type DocumentType =
  | "ID_CARD"
  | "LICENSE"
  | "OTHER"
  | "CAMARA_COMERCIO"
  | "RUT"
  | "REGISTRATION";

export interface ApiSuccess<T> {
  status: boolean;
  message?: string;
  data: T;
}

export interface PresignedDocumentRequest {
  document_type: DocumentType;
  file_name: string;
  // Backend validates extensions in this field (not real MIME types).
  mime_type: string;
  file_size_bytes: number;
  commerce_id: number;
  replace_document_id?: number | null;
  // Backend expects string, even though the docs say boolean.
  versioning_enabled?: string;
  metadata?: Record<string, unknown> | null;
}

export interface PresignedDocumentResponse {
  upload_token: string;
  presigned_url: string;
  expires_in: number;
  path: string;
}

export interface ConfirmDocumentRequest {
  upload_token: string;
  s3_metadata: {
    etag: string;
    object_size: number;
    last_modified: string;
  };
}

export interface DocumentUploadResource {
  id: number;
  commerce_id?: number;
  document_type?: string;
  file_path: string;
  presigned_url?: string;
  upload_status: string;
  s3_etag?: string | null;
  s3_object_size?: number | null;
  s3_last_modified?: string | null;
  version_number?: number | null;
  expires_at?: string | null;
  uploaded_by_id?: number | null;
  failed_attempts?: number | null;
  mime_type?: string | null;
  verified?: boolean | null;
  uploaded_at?: string | null;
  verified_at?: string | null;
  created_at?: string;
  updated_at?: string;
}

// ============================================
// API Functions
// ============================================

/**
 * POST /api/v1/documents/presigned
 * Genera URL prefirmada para subir un documento
 */
export async function createPresignedDocument(
  payload: PresignedDocumentRequest
): Promise<ApiSuccess<PresignedDocumentResponse>> {
  return fetchWithErrorHandling<ApiSuccess<PresignedDocumentResponse>>(
    "/api/v1/documents/presigned",
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );
}

/**
 * PATCH /api/v1/documents/confirm
 * Confirma el upload del documento
 */
export async function confirmDocumentUpload(
  payload: ConfirmDocumentRequest
): Promise<ApiSuccess<DocumentUploadResource>> {
  return fetchWithErrorHandling<ApiSuccess<DocumentUploadResource>>(
    "/api/v1/documents/confirm",
    {
      method: "PATCH",
      body: JSON.stringify(payload),
    }
  );
}

/**
 * DELETE /api/v1/documents/{document}
 * Elimina un documento
 */
export async function deleteDocumentUpload(documentId: number): Promise<void> {
  await fetchWithErrorHandling<void>(`/api/v1/documents/${documentId}`,
    {
      method: "DELETE",
    }
  );
}

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
// ============================================
// Document Download Functions
// ============================================

/**
 * Estructura genérica de respuesta del backend
 * La mayoría de endpoints retornan { status, message, data }
 */
interface ApiResponse<T> {
  status: boolean;
  message: string;
  data: T;
}

/**
 * Tipo de respuesta del endpoint POST /api/v1/documents/{id}/download-url
 * El backend genera una URL presignada temporal (válida 15 minutos)
 */
export interface DownloadUrlResponse {
  document_id: number;
  url: string;
  expires_at: string;
  expired_in_seconds: number;
}

/**
 * POST /api/v1/documents/{id}/download-url
 * Obtiene URL presignada para descargar un documento
 * 
 * Patrón de descarga:
 * 1. Llamar a este endpoint para obtener URL presignada
 * 2. Descargar desde la URL presignada (sin autenticación)
 * 3. La URL expira en 15 minutos
 * 
 * @param documentId - ID del documento
 * @returns Promise con URL presignada y metadata
 */
export async function obtenerUrlDescargaDocumento(
  documentId: number
): Promise<DownloadUrlResponse> {
  // La respuesta del backend viene como { status, message, data: {...} }
  const response = await fetchWithErrorHandling<ApiResponse<DownloadUrlResponse>>(
    `/api/v1/documents/${documentId}/download-url`,
    {
      method: "POST",
    }
  );
  
  // Extraer el contenido real de la envuelta ApiResponse
  return response.data;
}

/**
 * Descarga un documento usando su ID
 * 
 * Pasos:
 * 1. Obtiene URL presignada desde backend
 * 2. Inicia descarga desde la URL presignada
 * 3. Limpia recursos después de descargar
 * 
 * @param documentId - ID del documento
 * @param nombreArchivo - Nombre para el archivo descargado (ej: "documento.pdf")
 */
export async function descargarDocumento(
  documentId: number,
  nombreArchivo: string = 'documento'
): Promise<void> {
  try {
    // Paso 1: Obtener URL presignada del backend
    const { url, expires_at } = await obtenerUrlDescargaDocumento(documentId);
    
    // Log para debugging
    console.log(`[Descarga] Documento ${documentId}: URL presignada obtenida, expira en ${expires_at}`);
    
    // Paso 2: Descargar desde la URL presignada
    // Nota: La URL presignada es de S3, NO necesita autenticación (already signed)
    const descargaResponse = await fetch(url);
    
    if (!descargaResponse.ok) {
      throw new Error(`Error en descarga: ${descargaResponse.statusText}`);
    }
    
    // Paso 3: Crear blob e iniciar descarga
    const blob = await descargaResponse.blob();
    const urlBlob = window.URL.createObjectURL(blob);
    const enlace = document.createElement('a');
    enlace.href = urlBlob;
    enlace.download = nombreArchivo;
    document.body.appendChild(enlace);
    enlace.click();
    
    // Cleanup: remover el enlace temporal y liberar el blob URL
    document.body.removeChild(enlace);
    window.URL.revokeObjectURL(urlBlob);
    
  } catch (error) {
    console.error('Error descargando documento:', error);
    throw error;
  }
}

/**
 * Descarga un documento usando su endpoint de descarga
 * Alternativa a descargarDocumento cuando ya tenemos el endpoint
 * 
 * NOTA: En el adaptador de CommerceDocument, usamos download.endpoint como URL
 * porque el patrón es:
 * 1. Frontend almacena endpoint: /api/v1/documents/{id}/download-url
 * 2. Componente extrae el ID del endpoint o usa document.id directamente
 * 3. Llama a descargarDocumento(documentId) con el ID
 *
 * @param downloadEndpoint - URL del endpoint de descarga
 * @param nombreArchivo - Nombre para el archivo descargado
 */
export async function descargarDocumentoPorEndpoint(
  downloadEndpoint: string,
  nombreArchivo: string = 'documento'
): Promise<void> {
  try {
    // Extraer el ID del endpoint si viene en la URL
    // Ej: /api/v1/documents/33/download-url -> 33
    const match = downloadEndpoint.match(/documents\/(\d+)\/download-url/);
    
    if (match && match[1]) {
      const documentId = parseInt(match[1], 10);
      await descargarDocumento(documentId, nombreArchivo);
    } else {
      throw new Error('No se pudo extraer el ID del documento del endpoint');
    }
  } catch (error) {
    console.error('Error descargando documento desde endpoint:', error);
    throw error;
  }
}

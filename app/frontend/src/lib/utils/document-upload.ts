/**
 * Helpers for presigned document uploads
 */

export const BACKEND_ALLOWED_EXTENSIONS = ["pdf", "jpg", "png", "docx", "jpeg"] as const;

export type BackendAllowedExtension = (typeof BACKEND_ALLOWED_EXTENSIONS)[number];

export interface UploadToPresignedUrlResult {
  etag: string;
  object_size: number;
  last_modified: string;
}

const MIME_TO_EXTENSION: Record<string, BackendAllowedExtension> = {
  "application/pdf": "pdf",
  "image/jpeg": "jpeg",
  "image/jpg": "jpg",
  "image/png": "png",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document": "docx",
};

/**
 * Backend expects extensions in `mime_type`, not real MIME types.
 */
export function getBackendMimeType(file: File): BackendAllowedExtension | null {
  const byMime = MIME_TO_EXTENSION[file.type];
  if (byMime) return byMime;

  const extension = getFileExtension(file.name);
  if (!extension) return null;

  return isBackendAllowedExtension(extension) ? extension : null;
}

export function isBackendAllowedExtension(
  extension: string
): extension is BackendAllowedExtension {
  return BACKEND_ALLOWED_EXTENSIONS.includes(extension as BackendAllowedExtension);
}

export async function uploadFileToPresignedUrl(
  file: File,
  presignedUrl: string,
  options: { contentType?: string; signal?: AbortSignal } = {}
): Promise<UploadToPresignedUrlResult> {
  const response = await fetch(presignedUrl, {
    method: "PUT",
    body: file,
    // ⚠️ IMPORTANTE: No incluir Content-Type header
    // La presigned URL solo firma el header 'host'
    // Si enviamos headers no firmados, MinIO rechaza con SignatureDoesNotMatch
    signal: options.signal,
  });

  if (!response.ok) {
    throw new Error(`Upload failed with status ${response.status}`);
  }

  const etag = response.headers.get("etag") || "";

  return {
    etag,
    object_size: file.size,
    last_modified: new Date().toISOString(),
  };
}

function getFileExtension(fileName: string): string | null {
  const dotIndex = fileName.lastIndexOf(".");
  if (dotIndex < 0) return null;

  return fileName.slice(dotIndex + 1).toLowerCase();
}

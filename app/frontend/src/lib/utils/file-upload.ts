/**
 * Utilidades para manejo de archivos
 */

/**
 * Tipos MIME permitidos para documentos
 */
export const ALLOWED_FILE_TYPES = [
  'application/pdf',
  'image/jpeg',
  'image/png',
  'image/jpg',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

/**
 * Extensiones permitidas
 */
export const ALLOWED_EXTENSIONS = ['.pdf', '.jpg', '.jpeg', '.png', '.doc', '.docx'];

/**
 * Tamaño máximo de archivo en MB
 */
export const MAX_FILE_SIZE_MB = 10;
export const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

/**
 * Valida si el archivo es de un tipo permitido
 */
export function isValidFileType(file: File): boolean {
  return ALLOWED_FILE_TYPES.includes(file.type) || 
         ALLOWED_EXTENSIONS.some(ext => file.name.toLowerCase().endsWith(ext));
}

/**
 * Valida si el archivo está dentro del tamaño permitido
 */
export function isValidFileSize(file: File): boolean {
  return file.size <= MAX_FILE_SIZE_BYTES;
}

/**
 * Genera un nombre de archivo consistente basado en timestamp y nombre original
 * Formato: {timestamp}_{originalName}
 */
export function generateFileName(originalFileName: string): string {
  const timestamp = Date.now();
  const ext = originalFileName.substring(originalFileName.lastIndexOf('.'));
  const nameWithoutExt = originalFileName.substring(0, originalFileName.lastIndexOf('.'));
  
  // Sanitizar nombre: remover caracteres especiales
  const sanitizedName = nameWithoutExt
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '_')
    .replace(/_+/g, '_')
    .slice(0, 50); // Limitar a 50 caracteres

  return `${timestamp}_${sanitizedName}${ext}`;
}

/**
 * Valida un archivo completamente
 * Retorna un objeto con { isValid, error }
 */
export function validateFile(file: File): { isValid: boolean; error?: string } {
  if (!isValidFileType(file)) {
    return {
      isValid: false,
      error: `Tipo de archivo no permitido. Tipos permitidos: ${ALLOWED_EXTENSIONS.join(', ')}`,
    };
  }

  if (!isValidFileSize(file)) {
    return {
      isValid: false,
      error: `El archivo supera el tamaño máximo permitido de ${MAX_FILE_SIZE_MB}MB`,
    };
  }

  return { isValid: true };
}

/**
 * Abre un diálogo de selección de archivo
 */
export function openFileDialog(accept: string = ALLOWED_EXTENSIONS.join(',')): Promise<File | null> {
  return new Promise((resolve) => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = accept;
    
    input.onchange = (e: Event) => {
      const target = e.target as HTMLInputElement;
      const file = target.files?.[0];
      resolve(file || null);
    };
    
    input.click();
  });
}

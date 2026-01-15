'use client';

import { useState, useCallback } from 'react';
import { toast } from 'sonner';
import { openFileDialog, validateFile, generateFileName } from '@/lib/utils/file-upload';

/**
 * Hook para manejar upload de archivos
 * Retorna: { fileName, isUploading, handleUpload, handleRemove }
 */
export function useFileUpload(onFileSelected?: (fileName: string | null) => void) {
  const [fileName, setFileName] = useState<string | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  /**
   * Maneja la selección de archivo
   */
  const handleUpload = useCallback(async () => {
    setIsUploading(true);
    
    try {
      // Abrir diálogo de selección
      const file = await openFileDialog();
      if (!file) {
        setIsUploading(false);
        return;
      }

      // Validar archivo
      const validation = validateFile(file);
      if (!validation.isValid) {
        toast.error('Archivo inválido', {
          description: validation.error,
        });
        setIsUploading(false);
        return;
      }

      // Generar nombre consistente
      const generatedFileName = generateFileName(file.name);
      
      // Simular upload (en producción, aquí iría la llamada a API)
      // Simulamos un delay de 500ms para simular la subida
      await new Promise(resolve => setTimeout(resolve, 500));

      // Guardar nombre del archivo
      setFileName(generatedFileName);
      onFileSelected?.(generatedFileName);

      toast.success('Archivo cargado correctamente', {
        description: generatedFileName,
      });
    } catch (error) {
      console.error('Error al subir archivo:', error);
      toast.error('Error al cargar archivo', {
        description: 'Ocurrió un error al intentar cargar el archivo.',
      });
    } finally {
      setIsUploading(false);
    }
  }, [onFileSelected]);

  /**
   * Maneja la eliminación del archivo
   */
  const handleRemove = useCallback(() => {
    setFileName(null);
    onFileSelected?.(null);
    toast.info('Archivo eliminado', {
      description: 'El archivo ha sido removido correctamente',
    });
  }, [onFileSelected]);

  return {
    fileName,
    isUploading,
    handleUpload,
    handleRemove,
  };
}

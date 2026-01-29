'use client';

import { Toaster } from 'sonner';

/**
 * Provider de Toaster para Sonner
 * Se encarga de renderizar los toasts en toda la aplicación
 */
export function ToasterProvider() {
  return (
    <Toaster
      position="top-right"
      richColors
      closeButton
      expand
      theme="light"
    />
  );
}

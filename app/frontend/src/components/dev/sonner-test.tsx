"use client";

/**
 * Componente de prueba para Sonner
 * Úsalo temporalmente en una página para verificar que los toasts funcionan
 * 
 * Ejemplo de uso en una página:
 * import { SonnerTest } from '@/components/dev/sonner-test';
 * 
 * export default function Page() {
 *   return (
 *     <>
 *       <SonnerTest />
 *       // tu contenido aquí
 *     </>
 *   );
 * }
 */

import { useEffect } from "react";
import { toast } from "sonner";

export function SonnerTest() {
  useEffect(() => {
    // Probar los diferentes tipos de toast
    const timers = [
      setTimeout(() => toast.success("✅ Toast de Éxito funciona"), 500),
      setTimeout(() => toast.error("❌ Toast de Error funciona"), 1500),
      setTimeout(() => toast.warning("⚠️ Toast de Advertencia funciona"), 2500),
      setTimeout(() => toast.info("ℹ️ Toast de Información funciona"), 3500),
    ];

    return () => timers.forEach(clearTimeout);
  }, []);

  return (
    <div className="p-4 bg-blue-100 border border-blue-400 rounded-lg mb-4">
      <p className="font-bold">🧪 Sonner Test: Verifica los toasts en la esquina superior derecha</p>
      <p className="text-sm text-gray-700 mt-2">Deberías ver 4 toasts en los próximos 4 segundos...</p>
    </div>
  );
}

/**
 * Componente de estado de error con opción de reintentar
 * Diseño amigable con colores brand
 */

import { AlertCircle } from "lucide-react";

interface ErrorStateProps {
  message?: string;
  onRetry?: () => void;
}

export function ErrorState({ 
  message = "Ocurrió un error al cargar los datos",
  onRetry 
}: ErrorStateProps) {
  return (
    <div className="flex flex-col items-center justify-center py-16 px-4">
      <div className="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4">
        <AlertCircle className="w-8 h-8 text-red-500" />
      </div>
      
      <h3 className="text-lg font-semibold text-[#1A1A1A] mb-2">
        Error al cargar
      </h3>
      
      <p className="text-sm text-[#6A6A6A] text-center mb-6 max-w-md">
        {message}
      </p>
      
      {onRetry && (
        <button
          onClick={onRetry}
          className="px-6 py-3 bg-[#4B236A] text-white rounded-xl font-medium hover:bg-[#5D2B7D] transition-colors shadow-lg hover:shadow-xl"
        >
          Reintentar
        </button>
      )}
    </div>
  );
}

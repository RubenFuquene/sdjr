"use client";

import { useEffect } from "react";

export default function ProviderBranchesError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error("Provider branches error:", error);
  }, [error]);

  return (
    <div className="p-6 md:p-8">
      <div className="rounded-[18px] border border-red-200 bg-white p-6 md:p-8 shadow-sm">
        <h2 className="text-xl md:text-2xl font-bold text-[#4B236A] mb-2">
          No pudimos cargar tus sucursales
        </h2>
        <p className="text-gray-600 mb-6">
          Ocurrió un error al consultar la información de sucursales. Intenta nuevamente.
        </p>
        <button
          onClick={reset}
          className="rounded-[14px] bg-[#4B236A] h-[52px] px-6 text-white hover:bg-[#5D2B7D] transition-colors"
          aria-label="Reintentar carga de sucursales"
        >
          Intentar nuevamente
        </button>
      </div>
    </div>
  );
}

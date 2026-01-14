"use client";

import { useEffect } from "react";

export default function ProviderError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error("Provider panel error:", error);
  }, [error]);

  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="text-center">
        <h2 className="mb-4 text-2xl font-bold text-[#4B236A]">
          Algo salió mal
        </h2>
        <p className="mb-6 text-gray-600">
          Ocurrió un error en el panel de proveedores
        </p>
        <button
          onClick={reset}
          className="rounded-xl bg-[#4B236A] px-6 py-3 text-white hover:bg-[#5D2B7D] transition-colors"
        >
          Intentar nuevamente
        </button>
      </div>
    </div>
  );
}

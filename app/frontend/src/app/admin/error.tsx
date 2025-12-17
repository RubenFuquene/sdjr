"use client";

import { useEffect } from "react";

export default function AdminError({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    console.error("Admin segment error:", error);
  }, [error]);

  return (
    <div className="bg-login-gradient flex min-h-screen items-center justify-center px-4 py-10 sm:py-16 md:py-20">
      <div className="w-full max-w-md rounded-2xl bg-white px-6 py-8 shadow-login-card sm:px-8 sm:py-10 text-center">
        <h1 className="text-lg font-semibold text-[var(--color-text)]">Ocurrió un problema</h1>
        <p className="mt-2 text-sm text-[var(--color-muted)]">Intenta recargar la página o volver al inicio.</p>
        <div className="mt-6 flex flex-col gap-3">
          <button
            onClick={reset}
            className="inline-flex h-11 items-center justify-center rounded-full bg-[var(--color-brand)] px-4 text-sm font-semibold text-white transition hover:bg-[var(--color-brand-600)]"
          >
            Reintentar
          </button>
          <a href="/" className="text-sm font-medium text-[var(--color-brand)] underline decoration-[var(--color-brand)] decoration-1 underline-offset-2">
            Ir al inicio
          </a>
        </div>
      </div>
    </div>
  );
}

"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { ArrowLeft, Mail } from "lucide-react";

import { requestPasswordReset } from "@/lib/api/auth";

export function AdminForgotPasswordForm() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [successMessage, setSuccessMessage] = useState(
    "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
  );

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) {
      setError("Ingresa tu correo corporativo.");
      return;
    }

    if (!emailRegex.test(normalizedEmail)) {
      setError("Ingresa un correo válido.");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const result = await requestPasswordReset({ email: normalizedEmail });
      setSuccessMessage(
        result.message ||
          "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
      );
      setSuccess(true);
    } catch (err) {
      const message = err instanceof Error ? err.message : "No se pudo procesar la solicitud.";
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="space-y-5" aria-live="polite">
        <div className="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
          {successMessage}
        </div>

        <Link
          href={`/admin/reset-password?email=${encodeURIComponent(email.trim().toLowerCase())}`}
          className="block w-full"
        >
          <button
            type="button"
            className="h-[52px] w-full rounded-xl bg-[var(--color-brand)] text-white shadow-lg transition hover:bg-[var(--color-brand-600)] hover:shadow-xl"
          >
            Ya tengo token de recuperación
          </button>
        </Link>

        <Link
          href="/admin/login"
          className="mx-auto flex w-fit items-center gap-2 text-sm font-medium text-[var(--color-brand)] transition hover:text-[var(--color-brand-600)]"
        >
          <ArrowLeft className="h-4 w-4" />
          Volver a iniciar sesión
        </Link>
      </div>
    );
  }

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
      <p className="text-sm text-[var(--color-muted)]">
        Ingresa tu correo corporativo y te enviaremos instrucciones para restablecer tu contraseña.
      </p>

      <label className="block">
        <span className="mb-2 block text-[var(--color-text)]">Correo Corporativo</span>
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-brand)]" />
          <input
            required
            type="email"
            name="email"
            autoComplete="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="admin@sumass.com"
            disabled={loading}
            className="h-[50px] w-full rounded-[14px] border border-[var(--color-border)] pl-11 pr-4 text-[#1A1A1A] placeholder:text-[#9CA3AF] transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
          />
        </div>
      </label>

      {error ? (
        <div
          className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
          role="alert"
          aria-live="assertive"
        >
          {error}
        </div>
      ) : null}

      <button
        type="submit"
        disabled={loading}
        className="h-[52px] w-full rounded-xl bg-[var(--color-brand)] text-white shadow-lg transition hover:bg-[var(--color-brand-600)] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-80"
      >
        {loading ? "Enviando solicitud..." : "Enviar instrucciones"}
      </button>

      <Link
        href="/admin/login"
        className="mx-auto flex w-fit items-center gap-2 text-sm font-medium text-[var(--color-brand)] transition hover:text-[var(--color-brand-600)]"
      >
        <ArrowLeft className="h-4 w-4" />
        Volver a iniciar sesión
      </Link>
    </form>
  );
}

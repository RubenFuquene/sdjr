"use client";

import { FormEvent, useMemo, useState } from "react";
import Link from "next/link";
import { ArrowLeft, KeyRound, Lock, Mail } from "lucide-react";

import { resetPassword } from "@/lib/api/auth";

interface AdminResetPasswordFormProps {
  initialEmail?: string;
  initialToken?: string;
}

export function AdminResetPasswordForm({
  initialEmail = "",
  initialToken = "",
}: AdminResetPasswordFormProps) {
  const [email, setEmail] = useState(initialEmail);
  const [token, setToken] = useState(initialToken);
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const isPasswordValid = useMemo(() => password.length >= 8, [password]);
  const passwordsMatch = useMemo(
    () => passwordConfirmation.length > 0 && passwordConfirmation === password,
    [password, passwordConfirmation]
  );

  const inputClassName =
    "h-[50px] w-full rounded-[14px] border border-[var(--color-border)] pl-11 pr-4 text-[#1A1A1A] placeholder:text-[#9CA3AF] transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]";

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const normalizedEmail = email.trim().toLowerCase();
    const normalizedToken = token.trim();

    if (!normalizedEmail || !normalizedToken || !password || !passwordConfirmation) {
      setError("Completa todos los campos para restablecer tu contraseña.");
      return;
    }

    if (!emailRegex.test(normalizedEmail)) {
      setError("Ingresa un correo válido.");
      return;
    }

    if (!isPasswordValid) {
      setError("La nueva contraseña debe tener al menos 8 caracteres.");
      return;
    }

    if (!passwordsMatch) {
      setError("Las contraseñas no coinciden.");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const result = await resetPassword({
        email: normalizedEmail,
        token: normalizedToken,
        password,
        password_confirmation: passwordConfirmation,
      });

      setSuccessMessage(result.message || "Contraseña restablecida correctamente.");
    } catch (err) {
      const message = err instanceof Error ? err.message : "No se pudo restablecer la contraseña.";
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  if (successMessage) {
    return (
      <div className="space-y-5" aria-live="polite">
        <div className="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
          {successMessage}
        </div>

        <Link href="/admin/login" className="block w-full">
          <button
            type="button"
            className="h-[52px] w-full rounded-xl bg-[var(--color-brand)] text-white shadow-lg transition hover:bg-[var(--color-brand-600)] hover:shadow-xl"
          >
            Ir a iniciar sesión
          </button>
        </Link>
      </div>
    );
  }

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
      <p className="text-sm text-[var(--color-muted)]">
        Ingresa el token recibido por correo y define tu nueva contraseña.
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
            className={inputClassName}
          />
        </div>
      </label>

      <label className="block">
        <span className="mb-2 block text-[var(--color-text)]">Token de Recuperación</span>
        <div className="relative">
          <KeyRound className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-brand)]" />
          <input
            required
            type="text"
            name="token"
            value={token}
            onChange={(e) => setToken(e.target.value)}
            placeholder="Token de recuperación"
            disabled={loading}
            className={inputClassName}
          />
        </div>
      </label>

      <label className="block">
        <span className="mb-2 block text-[var(--color-text)]">Nueva Contraseña</span>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-brand)]" />
          <input
            required
            type="password"
            name="password"
            autoComplete="new-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            disabled={loading}
            className={inputClassName}
          />
        </div>
        {!isPasswordValid && password.length > 0 && (
          <p className="mt-1 text-xs text-red-600">Debe tener al menos 8 caracteres.</p>
        )}
      </label>

      <label className="block">
        <span className="mb-2 block text-[var(--color-text)]">Confirmar Contraseña</span>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-brand)]" />
          <input
            required
            type="password"
            name="password_confirmation"
            autoComplete="new-password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            placeholder="••••••••"
            disabled={loading}
            className={inputClassName}
          />
        </div>
        {passwordConfirmation.length > 0 && !passwordsMatch && (
          <p className="mt-1 text-xs text-red-600">Las contraseñas no coinciden.</p>
        )}
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
        {loading ? "Restableciendo..." : "Restablecer contraseña"}
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

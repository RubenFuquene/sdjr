"use client";

import { FormEvent, useMemo, useState } from "react";
import Link from "next/link";
import { KeyRound, Lock, Mail } from "lucide-react";

import { resetPassword } from "@/lib/api/auth";
import { Button } from "@/components/app/ui/button";
import { Input } from "@/components/app/ui/input";

interface AppResetPasswordFormProps {
  initialEmail?: string;
  initialToken?: string;
}

export default function AppResetPasswordForm({
  initialEmail = "",
  initialToken = "",
}: AppResetPasswordFormProps) {
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
      <div className="space-y-4" aria-live="polite">
        <div className="rounded-xl border border-[#CDE7D6] bg-[#F3FBF6] px-4 py-3">
          <p className="text-sm text-[#2F6F3E]">{successMessage}</p>
        </div>

        <Link href="/app/login" className="block w-full">
          <Button
            type="button"
            className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
          >
            Ir a iniciar sesión
          </Button>
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-4" aria-live="polite">
      <div className="space-y-2">
        <h2 className="text-2xl font-bold tracking-tight text-[#1A1A1A]">Restablecer contraseña</h2>
        <p className="text-sm text-[#6A6A6A]">
          Ingresa el token recibido por correo y define tu nueva contraseña.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <Input
            id="app-reset-email"
            type="email"
            placeholder="Correo electrónico"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            disabled={loading}
            className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
            aria-label="Correo electrónico"
            autoComplete="email"
            required
          />
        </div>

        <div className="relative">
          <KeyRound className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <Input
            id="app-reset-token"
            type="text"
            placeholder="Token de recuperación"
            value={token}
            onChange={(event) => setToken(event.target.value)}
            disabled={loading}
            className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
            aria-label="Token de recuperación"
            required
          />
        </div>

        <div className="space-y-1">
          <div className="relative">
            <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
            <Input
              id="app-reset-password"
              type="password"
              placeholder="Nueva contraseña"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              disabled={loading}
              className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
              aria-label="Nueva contraseña"
              autoComplete="new-password"
              required
            />
          </div>
          {!isPasswordValid && password.length > 0 && (
            <p className="text-xs text-[#B9342D]">Debe tener al menos 8 caracteres.</p>
          )}
        </div>

        <div className="space-y-1">
          <div className="relative">
            <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
            <Input
              id="app-reset-password-confirmation"
              type="password"
              placeholder="Confirmar contraseña"
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              disabled={loading}
              className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
              aria-label="Confirmar contraseña"
              autoComplete="new-password"
              required
            />
          </div>
          {passwordConfirmation.length > 0 && !passwordsMatch && (
            <p className="text-xs text-[#B9342D]">Las contraseñas no coinciden.</p>
          )}
        </div>

        {error && (
          <p className="rounded-xl bg-[#FDF2F8] px-4 py-2 text-sm text-[#B9342D]" aria-live="polite">
            {error}
          </p>
        )}

        <Button
          type="submit"
          disabled={loading}
          className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
          aria-busy={loading}
        >
          {loading ? "Restableciendo..." : "Restablecer contraseña"}
        </Button>

        <Link
          href="/app/login"
          className="mx-auto flex w-fit items-center gap-2 text-sm font-medium text-[#5A1E6B] hover:text-[#7A2E9A]"
        >
          Volver a iniciar sesión
        </Link>
      </form>
    </div>
  );
}

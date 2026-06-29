"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { ArrowLeft, Mail } from "lucide-react";

import { requestPasswordReset } from "@/lib/api/auth";
import { Button } from "@/components/app/ui/button";
import { Input } from "@/components/app/ui/input";

interface AppForgotPasswordFormProps {
  onBackToLogin?: () => void;
}

export default function AppForgotPasswordForm({ onBackToLogin }: AppForgotPasswordFormProps) {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState(
    "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
  );
  const [success, setSuccess] = useState(false);

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) {
      setError("Por favor ingresa tu correo electrónico");
      return;
    }

    if (!emailRegex.test(normalizedEmail)) {
      setError("Por favor ingresa un correo válido");
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

  return (
    <div className="space-y-4" aria-live="polite">
      <div className="space-y-2">
        <h2 className="text-2xl font-bold tracking-tight text-[#1A1A1A]">Recuperar contraseña</h2>
        <p className="text-sm text-[#6A6A6A]">
          Ingresa tu correo y te enviaremos instrucciones para restablecer tu contraseña.
        </p>
      </div>

      {success ? (
        <div className="space-y-4">
          <div className="rounded-xl border border-[#CDE7D6] bg-[#F3FBF6] px-4 py-3">
            <p className="text-sm text-[#2F6F3E]">{successMessage}</p>
          </div>

          <Link
            href={`/app/reset-password?email=${encodeURIComponent(email.trim().toLowerCase())}`}
            className="block w-full"
          >
            <Button
              type="button"
              className="h-12 w-full rounded-xl bg-[#DDE8BB] text-[#1A1A1A] hover:bg-[#C8D86D]"
            >
              Ya tengo token de recuperación
            </Button>
          </Link>

          <Button
            type="button"
            onClick={onBackToLogin}
            className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
          >
            Volver a iniciar sesión
          </Button>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="relative">
            <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
            <Input
              id="app-forgot-email"
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
            {loading ? "Enviando solicitud..." : "Enviar instrucciones"}
          </Button>

          <button
            type="button"
            onClick={onBackToLogin}
            className="mx-auto flex items-center gap-2 text-sm font-medium text-[#5A1E6B] hover:text-[#7A2E9A]"
          >
            <ArrowLeft className="h-4 w-4" />
            Volver a iniciar sesión
          </button>
        </form>
      )}
    </div>
  );
}
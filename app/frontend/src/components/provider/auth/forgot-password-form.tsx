"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { ArrowLeft, Mail } from "lucide-react";
import { requestPasswordReset } from "@/lib/api/auth";
import { Button } from "@/components/provider/ui/button";
import { Input } from "@/components/provider/ui/input";
import { Label } from "@/components/provider/ui/label";
import { cn } from "@/components/provider/ui/utils";

interface ForgotPasswordFormProps {
  onBackToLogin?: () => void;
}

export function ForgotPasswordForm({ onBackToLogin }: ForgotPasswordFormProps) {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState(
    "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
  );
  const [success, setSuccess] = useState(false);

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

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
    <div className="space-y-4">
      <div className="space-y-2">
        <h2 className="text-2xl font-bold tracking-tight text-[#1A1A1A]">Recuperar contraseña</h2>
        <p className="text-sm text-[#6A6A6A]">
          Ingresa tu correo y te enviaremos instrucciones para restablecer tu contraseña.
        </p>
      </div>

      {success ? (
        <div className="space-y-4">
          <div className="rounded-[12px] border border-emerald-200 bg-emerald-50 p-3">
            <p className="text-sm text-emerald-700">{successMessage}</p>
          </div>

          <Link
            href={`/reset-password?email=${encodeURIComponent(email.trim().toLowerCase())}`}
            className="block w-full"
          >
            <Button
              type="button"
              className={cn(
                "w-full h-[52px] rounded-[14px] font-medium",
                "bg-[#DDE8BB] text-[#1A1A1A] hover:bg-[#C8D86D]",
                "transition-all duration-200"
              )}
            >
              Ya tengo token de recuperación
            </Button>
          </Link>

          <Button
            type="button"
            onClick={onBackToLogin}
            className={cn(
              "w-full h-[52px] rounded-[14px] text-white font-medium",
              "bg-[#4B236A] hover:bg-[#5D2B7D]",
              "transition-all duration-200 shadow-lg hover:shadow-xl"
            )}
          >
            Volver a iniciar sesión
          </Button>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="forgot-email" className="text-[#1A1A1A] font-semibold">
              Correo Electrónico
            </Label>
            <div className="relative">
              <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
              <Input
                id="forgot-email"
                type="email"
                value={email}
                placeholder="correo@ejemplo.com"
                onChange={(e) => setEmail(e.target.value)}
                disabled={loading}
                className={cn(
                  "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
                  "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
                )}
              />
            </div>
          </div>

          {error && (
            <div className="rounded-[12px] bg-red-50 border border-red-200 p-3">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          <Button
            type="submit"
            disabled={loading}
            className={cn(
              "w-full h-[52px] rounded-[14px] text-white font-medium",
              "bg-[#4B236A] hover:bg-[#5D2B7D]",
              "disabled:opacity-50 disabled:cursor-not-allowed",
              "transition-all duration-200 shadow-lg hover:shadow-xl"
            )}
          >
            {loading ? "Enviando solicitud..." : "Enviar instrucciones"}
          </Button>

          <button
            type="button"
            onClick={onBackToLogin}
            className="mx-auto flex items-center gap-2 text-sm font-medium text-[#4B236A] hover:underline"
          >
            <ArrowLeft className="h-4 w-4" />
            Volver a iniciar sesión
          </button>
        </form>
      )}
    </div>
  );
}

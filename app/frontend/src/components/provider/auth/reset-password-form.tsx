"use client";

import { FormEvent, useMemo, useState } from "react";
import Link from "next/link";
import { ArrowLeft, KeyRound, Lock, Mail } from "lucide-react";
import { resetPassword } from "@/lib/api/auth";
import { Button } from "@/components/provider/ui/button";
import { Input } from "@/components/provider/ui/input";
import { Label } from "@/components/provider/ui/label";
import { cn } from "@/components/provider/ui/utils";

interface ResetPasswordFormProps {
  initialEmail?: string;
  initialToken?: string;
}

export function ResetPasswordForm({ initialEmail = "", initialToken = "" }: ResetPasswordFormProps) {
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

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

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

      setSuccessMessage(result.message || "Password reset successfully.");
    } catch (err) {
      const message = err instanceof Error ? err.message : "No se pudo restablecer la contraseña.";
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  if (successMessage) {
    return (
      <div className="space-y-4">
        <div className="rounded-[12px] border border-emerald-200 bg-emerald-50 p-3">
          <p className="text-sm text-emerald-700">{successMessage}</p>
        </div>

        <Link href="/provider/login" className="block w-full">
          <Button
            type="button"
            className={cn(
              "w-full h-[52px] rounded-[14px] text-white font-medium",
              "bg-[#4B236A] hover:bg-[#5D2B7D]",
              "transition-all duration-200 shadow-lg hover:shadow-xl"
            )}
          >
            Ir a iniciar sesión
          </Button>
        </Link>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="space-y-2">
        <h1 className="text-2xl font-bold tracking-tight text-[#1A1A1A]">Restablecer contraseña</h1>
        <p className="text-sm text-[#6A6A6A]">
          Ingresa el correo y token recibido para definir tu nueva contraseña.
        </p>
      </div>

      <div className="space-y-2">
        <Label htmlFor="reset-email" className="text-[#1A1A1A] font-semibold">
          Correo Electrónico
        </Label>
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="reset-email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            disabled={loading}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label htmlFor="reset-token" className="text-[#1A1A1A] font-semibold">
          Token de Recuperación
        </Label>
        <div className="relative">
          <KeyRound className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="reset-token"
            type="text"
            value={token}
            onChange={(e) => setToken(e.target.value)}
            disabled={loading}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label htmlFor="reset-password" className="text-[#1A1A1A] font-semibold">
          Nueva Contraseña
        </Label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="reset-password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            disabled={loading}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
          />
        </div>
        {!isPasswordValid && password.length > 0 && (
          <p className="text-xs text-red-600">Debe tener al menos 8 caracteres.</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="reset-password-confirmation" className="text-[#1A1A1A] font-semibold">
          Confirmar Contraseña
        </Label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="reset-password-confirmation"
            type="password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            disabled={loading}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
          />
        </div>
        {passwordConfirmation.length > 0 && !passwordsMatch && (
          <p className="text-xs text-red-600">Las contraseñas no coinciden.</p>
        )}
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
        {loading ? "Restableciendo..." : "Restablecer contraseña"}
      </Button>

      <Link href="/provider/login" className="mx-auto flex w-fit items-center gap-2 text-sm font-medium text-[#4B236A] hover:underline">
        <ArrowLeft className="h-4 w-4" />
        Volver a iniciar sesión
      </Link>
    </form>
  );
}
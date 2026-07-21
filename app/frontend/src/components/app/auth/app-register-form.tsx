"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Lock, Mail, User } from "lucide-react";

import { useAppAuthForm } from "@/hooks/app/use-app-auth-form";
import { PASSWORD_MIN_LENGTH, validatePasswordPolicy } from "@/lib/auth/password-policy";
import { Button } from "@/components/app/ui/button";
import { Input } from "@/components/app/ui/input";

const PASSWORD_MISMATCH_ERROR = "Las contrasenas no coinciden. Verifica ambos campos.";
const PASSWORD_POLICY_ERROR = "La contraseña aun no cumple todos los criterios de seguridad.";

export default function AppRegisterForm() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const { handleRegister, loading, error, clearError } = useAppAuthForm();
  const router = useRouter();
  const passwordPolicy = validatePasswordPolicy(password);
  const hasWeakPassword = password.length > 0 && !passwordPolicy.isStrong;
  const hasPasswordMismatch =
    confirmPassword.length > 0 && password !== confirmPassword;
  const resolvedErrorMessage = hasPasswordMismatch
    ? PASSWORD_MISMATCH_ERROR
    : hasWeakPassword
      ? PASSWORD_POLICY_ERROR
      : error;

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    clearError();

    if (hasPasswordMismatch || hasWeakPassword) {
      return;
    }

    try {
      const result = await handleRegister(name, email, password, confirmPassword);
      router.push(result.redirectTo || "/app/dashboard");
    } catch {
      // Hook already maps and stores API errors.
    }
  };

  return (
    <>
      <form onSubmit={handleSubmit} className="space-y-4" aria-live="polite">
        <div className="relative">
          <User className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <Input
            type="text"
            placeholder="Nombre completo"
            value={name}
            onChange={(event) => setName(event.target.value)}
            className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
            aria-label="Nombre completo"
            autoComplete="name"
            required
          />
        </div>

        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <Input
            type="email"
            placeholder="Correo electrónico"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
            aria-label="Correo electrónico"
            autoComplete="email"
            required
          />
        </div>

        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <button
            type="button"
            onClick={() => setShowPassword((prev) => !prev)}
            disabled={loading}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-[#7A2E9A] disabled:opacity-50"
            aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
          <Input
            type={showPassword ? "text" : "password"}
            placeholder="Contraseña"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            className={`h-12 rounded-xl bg-white pl-10 pr-10 focus:ring-[#5A1E6B] ${
              hasWeakPassword
                ? "border-[#B9342D] focus:border-[#B9342D]"
                : "border-[#E6E6E6] focus:border-[#5A1E6B]"
            }`}
            aria-label="Contraseña"
            aria-invalid={hasWeakPassword}
            autoComplete="new-password"
            required
          />
        </div>

        <div className="rounded-xl border border-[#EADCF1] bg-[#FCF8FE] px-4 py-3">
          <p className="mb-2 text-xs font-semibold text-[#5A1E6B]">Criterios de seguridad</p>
          <div className="grid grid-cols-1 gap-1 text-xs sm:grid-cols-2">
            <p className={passwordPolicy.minLength ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.minLength ? "OK" : "-"} Minimo {PASSWORD_MIN_LENGTH} caracteres
            </p>
            <p className={passwordPolicy.hasUppercase ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.hasUppercase ? "OK" : "-"} Una mayuscula
            </p>
            <p className={passwordPolicy.hasLowercase ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.hasLowercase ? "OK" : "-"} Una minuscula
            </p>
            <p className={passwordPolicy.hasNumber ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.hasNumber ? "OK" : "-"} Un numero
            </p>
            <p className={passwordPolicy.hasSymbol ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.hasSymbol ? "OK" : "-"} Un simbolo
            </p>
            <p className={passwordPolicy.noSpaces ? "text-[#2F6F3E]" : "text-[#7A2E9A]"}>
              {passwordPolicy.noSpaces ? "OK" : "-"} Sin espacios
            </p>
          </div>
        </div>

        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
          <button
            type="button"
            onClick={() => setShowPassword((prev) => !prev)}
            disabled={loading}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-[#7A2E9A] disabled:opacity-50"
            aria-label={showPassword ? "Ocultar contrasena" : "Mostrar contrasena"}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
          <Input
            type={showPassword ? "text" : "password"}
            placeholder="Repetir contrasena"
            value={confirmPassword}
            onChange={(event) => setConfirmPassword(event.target.value)}
            className={`h-12 rounded-xl bg-white pl-10 pr-10 focus:ring-[#5A1E6B] ${
              hasPasswordMismatch
                ? "border-[#B9342D] focus:border-[#B9342D]"
                : "border-[#E6E6E6] focus:border-[#5A1E6B]"
            }`}
            aria-label="Repetir contrasena"
            aria-invalid={hasPasswordMismatch}
            autoComplete="new-password"
            required
          />
        </div>

        {resolvedErrorMessage && (
          <p className="rounded-xl bg-[#FDF2F8] px-4 py-2 text-sm text-[#B9342D]" aria-live="polite">
            {resolvedErrorMessage}
          </p>
        )}

        <Button
          type="submit"
          className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
          disabled={loading || hasWeakPassword || hasPasswordMismatch}
          aria-busy={loading}
        >
          {loading ? "Creando cuenta..." : "Crear cuenta"}
        </Button>
      </form>

      <p className="mt-6 text-center text-xs text-[#7A2E9A]">
        Al registrarte, aceptas nuestros {" "}
        <button
          type="button"
          className="text-[#5A1E6B] underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5A1E6B]"
        >
          Términos y Condiciones
        </button>{" "}
        y {" "}
        <button
          type="button"
          className="text-[#5A1E6B] underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5A1E6B]"
        >
          Política de Privacidad
        </button>
      </p>
    </>
  );
}

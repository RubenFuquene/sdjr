"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { Eye, EyeOff, Mail } from "lucide-react";

import { useAppAuthForm } from "@/hooks/app/use-app-auth-form";
import { Button } from "@/components/app/ui/button";
import { Input } from "@/components/app/ui/input";

interface AppLoginFormProps {
  onForgotPassword?: () => void;
}

export default function AppLoginForm({ onForgotPassword }: AppLoginFormProps) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const { handleLogin, loading, error, clearError } = useAppAuthForm();
  const router = useRouter();
  const searchParams = useSearchParams();

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    clearError();

    try {
      const result = await handleLogin(email, password);
      const redirectTo = searchParams.get("redirectTo");
      router.push(redirectTo || result.redirectTo || "/app");
    } catch {
      // Hook already maps and stores API errors.
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4" aria-live="polite">
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
        <button
          type="button"
          onClick={() => setShowPassword((prev) => !prev)}
          disabled={loading}
          className="absolute left-3 top-1/2 -translate-y-1/2 text-[#7A2E9A]"
          aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
        >
          {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
        </button>
        <Input
          type={showPassword ? "text" : "password"}
          placeholder="Contraseña"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
          aria-label="Contraseña"
          autoComplete="current-password"
          required
        />
      </div>

      <div className="text-right">
        <button
          type="button"
          onClick={onForgotPassword}
          disabled={loading}
          className="text-sm text-[#5A1E6B] hover:text-[#7A2E9A] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5A1E6B]"
        >
          ¿Olvidaste tu contraseña?
        </button>
      </div>

      {error && (
        <p className="rounded-xl bg-[#FDF2F8] px-4 py-2 text-sm text-[#B9342D]" aria-live="polite">
          {error}
        </p>
      )}

      <Button
        type="submit"
        className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
        disabled={loading}
        aria-busy={loading}
      >
        {loading ? "Iniciando sesión..." : "Iniciar sesión"}
      </Button>
    </form>
  );
}

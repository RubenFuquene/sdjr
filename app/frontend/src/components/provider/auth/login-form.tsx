"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import { Mail, Lock } from "lucide-react";
import { useAuthForm } from "@/hooks/use-auth-form";
import { Button } from "@/components/provider/ui/button";
import { Input } from "@/components/provider/ui/input";
import { Label } from "@/components/provider/ui/label";
import { cn } from "@/components/provider/ui/utils";

export function LoginForm() {
  const router = useRouter();
  const { handleLogin, loading, error, clearError } = useAuthForm();
  
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    clearError();

    // Validación básica
    if (!email || !password) {
      // El hook muestra el error en su estado, pero podríamos validar aquí también
      return;
    }

    try {
      // Usar el hook para login
      const { redirectTo } = await handleLogin(email, password);
      
      // Login exitoso - redirigir al dashboard
      router.push(redirectTo);
    } catch {
      // El error ya está en el estado del hook (mostrado en UI)
      // No hacemos nada aquí, el componente lo renderiza
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {/* Email field */}
      <div className="space-y-2">
        <Label htmlFor="email" className="text-[#1A1A1A] font-semibold">
          Correo Electrónico
        </Label>
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="email"
            type="email"
            placeholder="correo@ejemplo.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
            disabled={loading}
          />
        </div>
      </div>

      {/* Password field */}
      <div className="space-y-2">
        <Label htmlFor="password" className="text-[#1A1A1A] font-semibold">
          Contraseña
        </Label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="password"
            type="password"
            placeholder="••••••••"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className={cn(
              "pl-10 h-[50px] rounded-[14px] border-[#E0E0E0] placeholder-gray-400",
              "focus:border-[#4B236A] focus:ring-2 focus:ring-[#4B236A]/20"
            )}
            disabled={loading}
          />
        </div>
      </div>

      {/* Error message */}
      {error && (
        <div className="rounded-[12px] bg-red-50 border border-red-200 p-3">
          <p className="text-sm text-red-700">{error}</p>
        </div>
      )}

      {/* Submit button */}
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
        {loading ? "Iniciando sesión..." : "Iniciar Sesión"}
      </Button>

      {/* Forgot password link */}
      <p className="text-center text-sm text-[#6A6A6A]">
        <a href="#" className="font-medium text-[#4B236A] hover:underline">
          ¿Olvidaste tu contraseña?
        </a>
      </p>
    </form>
  );
}

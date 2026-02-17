"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { Eye, EyeOff, Mail, User } from "lucide-react";

import { login } from "@/lib/api/auth";
import { persistSession } from "@/lib/session";
import { Button } from "@/components/app/ui/button";
import { Input } from "@/components/app/ui/input";

type ActiveTab = "login" | "register";

type FormValues = {
  name: string;
  email: string;
  password: string;
};

export default function AppLoginForm() {
  const [activeTab, setActiveTab] = useState<ActiveTab>("login");
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const router = useRouter();
  const searchParams = useSearchParams();

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setErrorMessage(null);

    if (activeTab === "register") {
      setErrorMessage("Registro aun no disponible en el modulo app.");
      return;
    }

    setIsSubmitting(true);

    try {
      const result = await login({ email, password });
      if (result.user) {
        persistSession(result.user);
      }

      const redirectTo = searchParams.get("redirectTo");
      router.push(redirectTo || result.redirectTo || "/app");
    } catch (error) {
      const message = error instanceof Error ? error.message : "No se pudo iniciar sesion";
      setErrorMessage(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="px-6 py-6">
      <div className="mb-6 flex gap-1 rounded-2xl bg-[#F5F5F5] p-1">
        <button
          type="button"
          onClick={() => setActiveTab("login")}
          disabled={isSubmitting}
          aria-pressed={activeTab === "login"}
          className={`flex-1 rounded-xl py-3 text-sm font-medium transition-colors ${
            activeTab === "login"
              ? "bg-[#5A1E6B] text-white"
              : "text-[#7A2E9A] hover:bg-[#DDE8BB]"
          }`}
        >
          Iniciar sesión
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("register")}
          disabled={isSubmitting}
          aria-pressed={activeTab === "register"}
          className={`flex-1 rounded-xl py-3 text-sm font-medium transition-colors ${
            activeTab === "register"
              ? "bg-[#5A1E6B] text-white"
              : "text-[#7A2E9A] hover:bg-[#DDE8BB]"
          }`}
        >
          Registrarse
        </button>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4" aria-live="polite">
        {activeTab === "register" && (
          <div className="relative">
            <User className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7A2E9A]" />
            <Input
              type="text"
              placeholder="Nombre completo"
              value={name}
              onChange={(event) => setName(event.target.value)}
              className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
              aria-label="Nombre completo"
              required
            />
          </div>
        )}

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
            className="absolute left-3 top-1/2 -translate-y-1/2 text-[#7A2E9A]"
            aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
          >
            {showPassword ? (
              <EyeOff className="h-5 w-5" />
            ) : (
              <Eye className="h-5 w-5" />
            )}
          </button>
          <Input
            type={showPassword ? "text" : "password"}
            placeholder="Contraseña"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            className="h-12 rounded-xl border-[#E6E6E6] bg-white pl-10 focus:border-[#5A1E6B] focus:ring-[#5A1E6B]"
            aria-label="Contraseña"
            autoComplete={activeTab === "login" ? "current-password" : "new-password"}
            required
          />
        </div>

        {activeTab === "login" && (
          <div className="text-right">
            <button
              type="button"
              className="text-sm text-[#5A1E6B] hover:text-[#7A2E9A] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5A1E6B]"
            >
              ¿Olvidaste tu contraseña?
            </button>
          </div>
        )}

        {errorMessage && (
          <p className="rounded-xl bg-[#FDF2F8] px-4 py-2 text-sm text-[#B9342D]" aria-live="polite">
            {errorMessage}
          </p>
        )}

        <Button
          type="submit"
          className="h-12 w-full rounded-xl bg-[#5A1E6B] text-white hover:bg-[#7A2E9A]"
          disabled={isSubmitting}
          aria-busy={isSubmitting}
        >
          {activeTab === "login"
            ? isSubmitting
              ? "Iniciando sesión..."
              : "Iniciar sesión"
            : isSubmitting
              ? "Creando cuenta..."
              : "Crear cuenta"}
        </Button>
      </form>

      {activeTab === "register" && (
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
      )}
    </div>
  );
}

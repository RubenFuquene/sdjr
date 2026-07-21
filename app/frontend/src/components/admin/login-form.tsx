"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Lock, Mail } from "lucide-react";
import { login } from "@/lib/api/index";
import type { SessionData } from "@/types/auth";
import { persistSession } from "@/lib/session";

type LoginFormProps = {
  // Optional hook for future customization; expected to be passed from a Client Component only
  onSubmit?: (data: { email: string; password: string }) => Promise<void>;
  labels: {
    emailLabel: string;
    emailPlaceholder: string;
    passwordLabel: string;
    passwordPlaceholder: string;
    forgot: string;
    submit: string;
    errorGeneric: string;
  };
};

export function LoginForm({ onSubmit, labels }: LoginFormProps) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);
    setLoading(true);
    try {
      let redirectTo = "/admin/dashboard";
      let sessionData: SessionData;

      if (onSubmit) {
        await onSubmit({ email, password });
        // Para onSubmit personalizado, crear datos básicos
        sessionData = {
          userId: "unknown",
          email: email,
          role: "admin",
          name: email.split('@')[0],
          last_name: ''
        };
      } else {
        const result = await login({ email, password, scope: "admin" });
        redirectTo = result.redirectTo ?? redirectTo;
        
        // Usar datos del usuario de la respuesta de la API
        sessionData = result.user || {
          userId: "unknown", 
          email: email,
          role: "admin",
          name: email.split('@')[0],
          last_name: '',
          token: result.token
        };
      }

      persistSession(sessionData);

      router.push(redirectTo);
    } catch (err) {
      console.error(err);
      const message = err instanceof Error ? err.message : labels.errorGeneric;
      setError(message || labels.errorGeneric);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
      <label className="block">
        <span className="block text-[var(--color-text)] mb-2">{labels.emailLabel}</span>
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-brand)]" />
          <input
            required
            type="email"
            name="email"
            autoComplete="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder={labels.emailPlaceholder}
            className="w-full h-[50px] pl-11 pr-4 border border-[var(--color-border)] rounded-[14px] text-[#1A1A1A] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] focus:border-transparent transition"
          />
        </div>
      </label>

      <div className="block">
        <label htmlFor="admin-login-password" className="block text-[var(--color-text)] mb-2">
          {labels.passwordLabel}
        </label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-brand)]" />
          <button
            type="button"
            onClick={() => setShowPassword((prev) => !prev)}
            disabled={loading}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-[var(--color-brand)] disabled:opacity-50"
            aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
          <input
            id="admin-login-password"
            required
            type={showPassword ? "text" : "password"}
            name="password"
            autoComplete="current-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder={labels.passwordPlaceholder}
            className="w-full h-[50px] pl-11 pr-10 border border-[var(--color-border)] rounded-[14px] text-[#1A1A1A] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] focus:border-transparent transition"
          />
        </div>
      </div>

      <div className="text-right">
        <Link
          href="/admin/forgot-password"
          className="text-[var(--color-brand)] hover:text-[var(--color-brand-600)] transition text-sm"
        >
          {labels.forgot}
        </Link>
      </div>

      {error ? (
        <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert" aria-live="assertive">
          {error}
        </div>
      ) : null}

      <button
        type="submit"
        disabled={loading}
        className="w-full h-[52px] bg-[var(--color-brand)] text-white rounded-xl hover:bg-[var(--color-brand-600)] transition shadow-lg hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-80"
      >
        {loading ? `${labels.submit}...` : labels.submit}
      </button>
    </form>
  );
}

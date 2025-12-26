"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import { login } from "@/lib/api";
import type { SessionData } from "@/types/auth";
import { mapLaravelRoleToRole } from "@/lib/roles";
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
        const result = await login({ email, password });
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
          <MailIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-brand)]" />
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

      <label className="block">
        <span className="block text-[var(--color-text)] mb-2">{labels.passwordLabel}</span>
        <div className="relative">
          <LockIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-brand)]" />
          <input
            required
            type="password"
            name="password"
            autoComplete="current-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder={labels.passwordPlaceholder}
            className="w-full h-[50px] pl-11 pr-4 border border-[var(--color-border)] rounded-[14px] text-[#1A1A1A] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] focus:border-transparent transition"
          />
        </div>
      </label>

      <div className="text-right">
        <a href="#" className="text-[var(--color-brand)] hover:text-[var(--color-brand-600)] transition text-sm">
          {labels.forgot}
        </a>
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

function MailIcon({ className }: { className?: string }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
      className={className}
      aria-hidden="true"
    >
      <path d="M4.75 5.75h14.5a1 1 0 0 1 1 1v10.5a1 1 0 0 1-1 1H4.75a1 1 0 0 1-1-1V6.75a1 1 0 0 1 1-1Z" />
      <path d="m4 7 8 5 8-5" />
    </svg>
  );
}

function LockIcon({ className }: { className?: string }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
      className={className}
      aria-hidden="true"
    >
      <rect x="5.5" y="10.5" width="13" height="9" rx="2" />
      <path d="M9 10.5V7.75a3 3 0 0 1 6 0v2.75" />
    </svg>
  );
}

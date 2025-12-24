"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import { login } from "@/lib/api";

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

  const persistSession = (userData: any, token?: string) => {
    // Crear datos de sesión basados en la respuesta de la API
    const sessionData = {
      userId: userData.id?.toString() || "unknown",
      email: userData.email || "",
      role: mapLaravelRoleToRole(userData.rol),
      name: userData.name,
      last_name: userData.last_name,
      token: token
    };
    
    // Guardar en cookie para que el middleware pueda leerlo
    const maxAge = 60 * 60 * 24 * 7; // 1 semana
    const encodedData = encodeURIComponent(JSON.stringify(sessionData));
    document.cookie = `sdjr_session=${encodedData}; path=/; max-age=${maxAge}; SameSite=Lax`;
  };

  const mapLaravelRoleToRole = (laravelRole: string) => {
    switch (laravelRole) {
      case "provider":
        return "provider";
      case "customer":
        return "app";
      case "admin":
      default:
        return "admin";
    }
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);
    setLoading(true);
    try {
      let redirectTo = "/admin/dashboard";
      let userData: any = null;
      let token: string | undefined;

      if (onSubmit) {
        await onSubmit({ email, password });
        // Para onSubmit personalizado, crear datos básicos
        userData = {
          id: "unknown",
          email: email,
          rol: "admin",
          name: email.split('@')[0],
          last_name: ''
        };
      } else {
        const result = await login({ email, password });
        token = result.token;
        redirectTo = result.redirectTo ?? redirectTo;
        // Usar datos del usuario de la respuesta de la API
        userData = result.user || {
          id: "unknown", 
          email: email,
          rol: redirectTo.includes('/admin/') ? 'admin' : 
               redirectTo.includes('/provider/') ? 'provider' : 'customer',
          name: email.split('@')[0],
          last_name: ''
        };
      }

      persistSession(userData, token);

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
    <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
      <label className="flex flex-col gap-2 text-sm font-medium text-[var(--color-text)]">
        <span>{labels.emailLabel}</span>
        <div className="flex items-center gap-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-3 text-[var(--color-text)] shadow-sm focus-within:border-[var(--color-brand)] focus-within:ring-2 focus-within:ring-[color:var(--color-brand)]/10">
          <MailIcon className="h-4 w-4 text-[var(--color-muted)]" />
          <input
            required
            type="email"
            name="email"
            autoComplete="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder={labels.emailPlaceholder}
            className="w-full bg-transparent text-sm outline-none placeholder:text-[var(--color-muted)]"
          />
        </div>
      </label>

      <label className="flex flex-col gap-2 text-sm font-medium text-[var(--color-text)]">
        <span>{labels.passwordLabel}</span>
        <div className="flex items-center gap-2 rounded-lg border border-[var(--color-border)] bg-white px-3 py-3 text-[var(--color-text)] shadow-sm focus-within:border-[var(--color-brand)] focus-within:ring-2 focus-within:ring-[color:var(--color-brand)]/10">
          <LockIcon className="h-4 w-4 text-[var(--color-muted)]" />
          <input
            required
            type="password"
            name="password"
            autoComplete="current-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder={labels.passwordPlaceholder}
            className="w-full bg-transparent text-sm outline-none placeholder:text-[var(--color-muted)]"
          />
        </div>
      </label>

      <div className="flex justify-end text-xs text-[var(--color-muted)]">
        <span aria-disabled="true" className="cursor-not-allowed">
          {labels.forgot}
        </span>
      </div>

      {error ? (
        <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert" aria-live="assertive">
          {error}
        </div>
      ) : null}

      <button
        type="submit"
        disabled={loading}
        className="mt-1 inline-flex h-12 w-full items-center justify-center rounded-full bg-[var(--color-brand)] px-4 text-sm font-semibold text-white transition hover:bg-[var(--color-brand-600)] disabled:cursor-not-allowed disabled:opacity-80"
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

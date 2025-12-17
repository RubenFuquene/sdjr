"use client";

import { useRouter } from "next/navigation";

export function LogoutButton() {
  const router = useRouter();

  const handleLogout = () => {
    // Dummy logout: limpia cookies de sesión hasta tener el endpoint real.
    // TODO: Reemplazar por llamada a POST /logout (Laravel) y dejar que el backend maneje las cookies HttpOnly.
    const cookiesToClear = ["sdjr_session", "sdjr_role", "sdjr_email"];
    cookiesToClear.forEach((name) => {
      document.cookie = `${name}=; path=/; max-age=0`;
    });
    router.push("/admin/login");
  };

  return (
    <button
      type="button"
      onClick={handleLogout}
      aria-label="Cerrar sesión"
      className="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border)] bg-white text-[var(--color-muted)] transition hover:text-[var(--color-brand)] hover:shadow-sm"
    >
      <LogoutIcon className="h-5 w-5" />
    </button>
  );
}

function LogoutIcon({ className }: { className?: string }) {
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
      <path d="M15 12H4" />
      <path d="m11 8-4 4 4 4" />
      <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" />
    </svg>
  );
}

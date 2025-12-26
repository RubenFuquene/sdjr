"use client";

import { useRouter } from "next/navigation";
import { LogOut } from "lucide-react";

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
      title="Cerrar sesión"
      className="p-2 text-[#4B236A] hover:bg-red-500/20 hover:text-red-600 rounded-xl transition"
    >
      <LogOut className="w-5 h-5" />
    </button>
  );
}

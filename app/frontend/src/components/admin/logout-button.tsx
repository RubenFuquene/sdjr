"use client";

import { useRouter } from "next/navigation";
import { LogOut } from "lucide-react";
import { clearSession } from "@/lib/session";

export function LogoutButton() {
  const router = useRouter();

  const handleLogout = () => {
    // Limpiar sesión usando función centralizada
    // TODO: Reemplazar por llamada a POST /logout (Laravel) cuando esté disponible
    clearSession();
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

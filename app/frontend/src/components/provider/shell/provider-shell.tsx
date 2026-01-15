"use client";

import Link from "next/link";
import Image from "next/image";
import { ReactNode, useState } from "react";
import { useRouter } from "next/navigation";
import {
  Store,
  Building2,
  Package,
  CreditCard,
  Scale,
  DollarSign,
  BarChart3,
  CheckCircle,
  Headphones,
  LogOut,
  Menu,
  X,
} from "lucide-react";
import { Button } from "@/components/provider/ui/button";
import { clearSession } from "@/lib/session";
import type { SessionData } from "@/types/auth";

const navItems = [
  { label: "Dashboard", href: "/provider/dashboard", icon: BarChart3 },
  { label: "Datos Básicos", href: "/provider/basic-info", icon: Store },
  { label: "Sucursales", href: "/provider/branches", icon: Building2 },
  { label: "Productos", href: "/provider/products", icon: Package },
  { label: "Mi Cuenta", href: "/provider/bank", icon: CreditCard },
  { label: "Legal", href: "/provider/legal", icon: Scale },
  { label: "Mi Billetera", href: "/provider/wallet", icon: DollarSign },
  { label: "Revisión", href: "/provider/review", icon: CheckCircle },
  { label: "Soporte", href: "/provider/support", icon: Headphones },
];

type ProviderShellProps = {
  children: ReactNode;
  activePath?: string;
  userData?: SessionData | null;
};

export function ProviderShell({
  children,
  activePath = "/provider/dashboard",
  userData,
}: ProviderShellProps) {
  const router = useRouter();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = () => {
    clearSession();
    setSidebarOpen(false);
    router.push("/provider/login");
  };

  // Determinar estado del registro (por ahora hardcoded, futuro: desde backend)
  const registrationStatus = "Pendiente" as "Activo" | "Pendiente" | "Rechazado";
  const statusColor =
    registrationStatus === "Activo"
      ? "bg-green-500"
      : registrationStatus === "Pendiente"
        ? "bg-yellow-500"
        : "bg-red-500";

  // Obtener inicial del nombre
  const userInitial = userData?.name?.charAt(0).toUpperCase() || "U";

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Overlay backdrop */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 transition-opacity duration-200 md:hidden"
          onClick={() => setSidebarOpen(false)}
          aria-hidden="true"
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-50 w-64 flex-col bg-[#DDE8BB] transform transition-transform duration-300 ease-in-out overflow-y-auto flex-shrink-0 md:relative md:translate-x-0 md:flex ${
          sidebarOpen ? "translate-x-0 flex" : "-translate-x-full"
        }`}
      >
        {/* Header del Sidebar */}
        <div className="px-6 py-6 border-b border-[#4B236A]/10 flex-shrink-0 relative">
          {/* Close button - solo mobile */}
          <button
            onClick={() => setSidebarOpen(false)}
            className="absolute top-4 right-4 p-2 text-[#4B236A] hover:bg-[#4B236A]/10 rounded-lg md:hidden z-10"
            aria-label="Cerrar menú"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="flex items-center gap-3">
            {/* Logo */}
            <div className="w-12 h-12 rounded-[10px] bg-white flex items-center justify-center p-1">
              <Image
                src="/brand/logo-sumass.png"
                alt="Sumass"
                width={48}
                height={48}
                className="w-full h-full object-contain"
              />
            </div>
            {/* Texto */}
            <div className="leading-tight">
              <h1 className="text-[#4B236A] text-xl font-semibold">Sumass</h1>
              <p className="text-[#4B236A]/70 text-xs">Tu Sumass al planeta</p>
            </div>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 px-4 py-4 overflow-y-auto">
          <div className="flex flex-col gap-2">
            {navItems.map((item) => {
              const isActive = activePath.startsWith(item.href);
              const Icon = item.icon;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setSidebarOpen(false)}
                  className={`flex items-center gap-3 px-4 py-3 rounded-[14px] text-sm font-medium transition-all duration-200 ${
                    isActive
                      ? "bg-[#4B236A] text-white shadow-md"
                      : "text-[#4B236A] hover:bg-[#4B236A]/10"
                  }`}
                >
                  <Icon className="w-5 h-5 flex-shrink-0" />
                  <span className="text-left">{item.label}</span>
                </Link>
              );
            })}
          </div>
        </nav>

        {/* Footer */}
        <div className="px-4 py-4 border-t border-[#4B236A]/10 space-y-4 flex-shrink-0">
          {/* Status Card */}
          <div className="bg-[#4B236A]/10 rounded-[14px] p-4">
            <p className="text-sm text-[#4B236A]/70 mb-2">Estado del Registro</p>
            <div className="flex items-center gap-2">
              <div className={`w-2 h-2 rounded-full ${statusColor}`} />
              <span className="text-sm text-[#4B236A]">{registrationStatus}</span>
            </div>
          </div>

          {/* User Profile + Logout */}
          <div>
            <div className="flex items-center gap-3 mb-3">
              {/* Avatar */}
              <div className="w-8 h-8 rounded-full bg-[#4B236A] flex items-center justify-center flex-shrink-0">
                <span className="text-sm text-white font-medium">{userInitial}</span>
              </div>
              {/* User Info */}
              <div className="flex-1 min-w-0">
                <p className="text-sm text-[#4B236A] truncate">
                  {userData?.name || "Usuario"}
                </p>
                <p className="text-xs text-[#4B236A]/60 truncate">
                  {userData?.email || ""}
                </p>
              </div>
            </div>

            {/* Logout Button */}
            <Button
              variant="outline"
              onClick={handleLogout}
              className="w-full text-[#4B236A] border-[#4B236A]/30 hover:bg-[#4B236A] hover:text-white hover:border-[#4B236A] rounded-[14px] h-[48px] transition-all duration-200"
            >
              <LogOut className="w-4 h-4 mr-2" />
              Cerrar Sesión
            </Button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 bg-gray-50 overflow-auto min-h-0 min-w-0">
        {/* Hamburger button - solo mobile */}
        <div className="p-4 md:hidden">
          <button
            onClick={() => setSidebarOpen(true)}
            className="p-2 text-[#4B236A] hover:bg-[#4B236A]/10 rounded-lg transition-colors"
            aria-label="Abrir menú"
          >
            <Menu className="w-6 h-6" />
          </button>
        </div>

        {/* Content children */}
        {children}
      </main>
    </div>
  );
}

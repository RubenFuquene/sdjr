"use client";

import Link from "next/link";
import Image from "next/image";
import { ReactNode, useState } from "react";
import { LogoutButton } from "@/components/admin/logout-button";
import { Users, Settings, ShieldCheck, Megaphone, Headphones, Bell, User, BarChart3, Menu, X } from "lucide-react";

const navItems = [
  { label: "Perfiles", href: "/admin/dashboard", icon: Users },
  { label: "Parametrización", href: "/admin/settings", icon: Settings },
  { label: "Validación de Proveedores", href: "/admin/providers", icon: ShieldCheck },
  { label: "Marketing", href: "/admin/marketing", icon: Megaphone },
  { label: "Analytics", href: "/admin/analytics", icon: BarChart3 },
  { label: "Soporte", href: "/admin/support", icon: Headphones },
];

type DashboardShellProps = {
  children: ReactNode;
  activePath?: string;
};

export function DashboardShell({ children, activePath = "/admin/dashboard" }: DashboardShellProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="flex h-screen bg-gray-50 text-[var(--color-text)]">
      {/* Overlay backdrop */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 transition-opacity duration-200 md:hidden"
          onClick={() => setSidebarOpen(false)}
          aria-hidden="true"
        />
      )}
      <aside
        className={`fixed inset-y-0 left-0 z-50 w-64 flex-col border-r border-[#C8D86D] bg-[#DDE8BB] shadow-xl transform transition-transform duration-300 ease-in-out overflow-y-auto shrink-0 md:relative md:translate-x-0 md:flex ${
          sidebarOpen ? "translate-x-0 flex" : "-translate-x-full"
        }`}
      >
        {/* Close button for mobile */}
        <button
          onClick={() => setSidebarOpen(false)}
          className="absolute top-4 right-4 p-2 text-[#1A1A1A] hover:bg-[#C8D86D] rounded-lg md:hidden z-10"
          aria-label="Cerrar menú"
        >
          <X className="w-5 h-5" />
        </button>
        <div className="flex items-center gap-3 px-6 py-6 border-b border-[#C8D86D] flex-shrink-0">
          <div className="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1">
            <Image src="/brand/logo-sumass.png" alt="Sumass" width={48} height={48} className="w-full h-full object-contain" />
          </div>
          <div className="leading-tight">
            <h1 className="text-sm font-semibold text-[#1A1A1A]">Sumass</h1>
            <p className="text-xs text-[#4B236A]">Tu Sumass al planeta</p>
          </div>
        </div>
        <nav className="flex flex-col gap-2 px-4 py-4 flex-1 overflow-y-auto">
          {navItems.map((item) => {
            const isActive = activePath.startsWith(item.href);
            const Icon = item.icon;
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setSidebarOpen(false)}
                className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all ${
                  isActive
                    ? "bg-[#4B236A] text-white shadow-lg"
                    : "text-[#1A1A1A]/80 hover:bg-[#C8D86D]"
                }`}
              >
                <Icon className="w-5 h-5 flex-shrink-0" />
                <span className="text-left">{item.label}</span>
              </Link>
            );
          })}
        </nav>
      </aside>

      <div className="flex flex-1 flex-col min-w-0">
        <header className="flex flex-col gap-4 border-b border-[#C8D86D] bg-[#DDE8BB] px-4 md:px-6 py-3 md:py-4 md:flex-row md:items-center md:justify-between shadow-sm">
          <div className="flex items-center gap-3">
            {/* Hamburger menu button */}
            <button
              onClick={() => setSidebarOpen(true)}
              className="p-2 text-[#4B236A] hover:bg-[#C8D86D] rounded-lg md:hidden"
              aria-label="Abrir menú"
            >
              <Menu className="w-6 h-6" />
            </button>
            <div>
              <h2 className="text-base font-semibold text-[#1A1A1A]">Bienvenido, Administrador</h2>
              <p className="text-sm text-[#6A6A6A]">Gestiona tu plataforma desde aquí</p>
            </div>
          </div>
          <div className="flex items-center gap-3 md:gap-4">
            <button className="relative p-2 text-[#4B236A] hover:bg-[#C8D86D] rounded-xl transition">
              <Bell className="w-5 h-5" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-[#4B236A] rounded-full"></span>
            </button>
            <div className="flex items-center gap-2 md:gap-3 px-3 md:px-4 py-2 bg-white/50 rounded-xl border border-[#C8D86D]">
              <div className="w-7 h-7 md:w-8 md:h-8 bg-[#4B236A] rounded-full flex items-center justify-center">
                <User className="w-4 h-4 text-white" />
              </div>
              <span className="hidden sm:inline text-sm font-medium text-[#1A1A1A]">Admin</span>
            </div>
            <LogoutButton />
          </div>
        </header>

        <main className="flex-1 bg-white px-4 py-6 md:px-6 overflow-y-auto min-h-0 min-w-0" style={{ scrollbarWidth: 'thin', scrollbarColor: '#C8D86D transparent' }}>{children}</main>
      </div>
    </div>
  );
}

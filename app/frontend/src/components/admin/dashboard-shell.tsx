import Link from "next/link";
import Image from "next/image";
import { ReactNode } from "react";
import { LogoutButton } from "@/components/admin/logout-button";
import { LayoutDashboard, Users, Settings, ShieldCheck, Megaphone, Headphones, Bell, User, BarChart3 } from "lucide-react";

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
  return (
    <div className="flex min-h-screen bg-gray-50 text-[var(--color-text)]">
      <aside className="hidden w-64 flex-col border-r border-[#C8D86D] bg-[#DDE8BB] px-4 py-6 md:flex shadow-xl">
        <div className="flex items-center gap-3 px-2 pb-6 border-b border-[#C8D86D]">
          <div className="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1">
            <Image src="/brand/logo-sumass.png" alt="Sumass" width={48} height={48} className="w-full h-full object-contain" />
          </div>
          <div className="leading-tight">
            <h1 className="text-sm font-semibold text-[#1A1A1A]">Sumass</h1>
            <p className="text-xs text-[#4B236A]">Tu Sumass al planeta</p>
          </div>
        </div>
        <nav className="flex flex-col gap-2">
          {navItems.map((item) => {
            const isActive = activePath.startsWith(item.href);
            const Icon = item.icon;
            return (
              <Link
                key={item.href}
                href={item.href}
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

      <div className="flex flex-1 flex-col">
        <header className="flex flex-col gap-4 border-b border-[#C8D86D] bg-[#DDE8BB] px-6 py-4 md:flex-row md:items-center md:justify-between shadow-sm">
          <div>
            <h2 className="text-base font-semibold text-[#1A1A1A]">Bienvenido, Administrador</h2>
            <p className="text-sm text-[#6A6A6A]">Gestiona tu plataforma desde aquí</p>
          </div>
          <div className="flex items-center gap-4">
            <button className="relative p-2 text-[#4B236A] hover:bg-[#C8D86D] rounded-xl transition">
              <Bell className="w-5 h-5" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-[#4B236A] rounded-full"></span>
            </button>
            <div className="flex items-center gap-3 px-4 py-2 bg-white/50 rounded-xl border border-[#C8D86D]">
              <div className="w-8 h-8 bg-[#4B236A] rounded-full flex items-center justify-center">
                <User className="w-4 h-4 text-white" />
              </div>
              <span className="text-sm font-medium text-[#1A1A1A]">Admin</span>
            </div>
            <LogoutButton />
          </div>
        </header>

        <MobileNav activePath={activePath} />

        <main className="flex-1 bg-white px-4 py-6 md:px-6">{children}</main>
      </div>
    </div>
  );
}

function MobileNav({ activePath }: { activePath: string }) {
  return (
    <nav className="flex items-center gap-2 overflow-x-auto border-b border-[#C8D86D] bg-white px-4 py-2 md:hidden">
      {navItems.map((item) => {
        const isActive = activePath.startsWith(item.href);
        const Icon = item.icon;
        return (
          <Link
            key={item.href}
            href={item.href}
            className={`whitespace-nowrap flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition ${
              isActive
                ? "bg-[#4B236A] text-white shadow-sm"
                : "border border-[#E0E0E0] text-[#1A1A1A] hover:border-[#4B236A] hover:text-[#4B236A]"
            }`}
          >
            <Icon className="w-4 h-4" />
            {item.label}
          </Link>
        );
      })}
    </nav>
  );
}

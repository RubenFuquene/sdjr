import Link from "next/link";
import Image from "next/image";
import { ReactNode } from "react";
import { LogoutButton } from "@/components/admin/logout-button";

const navItems = [
  { label: "Perfiles", href: "/admin/dashboard" },
  { label: "Parametrización", href: "/admin/settings" },
  { label: "Validación de Proveedores", href: "/admin/providers" },
  { label: "Marketing", href: "/admin/marketing" },
  { label: "Dashboard", href: "/admin/analytics" },
  { label: "Soporte", href: "/admin/support" },
];

type DashboardShellProps = {
  children: ReactNode;
  activePath?: string;
};

export function DashboardShell({ children, activePath = "/admin/dashboard" }: DashboardShellProps) {
  return (
    <div className="flex min-h-screen bg-[#f4f7e6] text-[var(--color-text)]">
      <aside className="hidden w-[230px] flex-col border-r border-[var(--color-border)] bg-[#e8efc8] px-4 py-6 md:flex">
        <div className="flex items-center gap-3 px-2 pb-6">
          <Image src="/brand/logo-su.svg" alt="Sumass" width={36} height={36} />
          <div className="leading-tight">
            <p className="text-sm font-semibold text-[var(--color-text)]">Sumass</p>
            <p className="text-xs text-[var(--color-muted)]">Tu Sumass al planeta</p>
          </div>
        </div>
        <nav className="flex flex-col gap-1">
          {navItems.map((item) => {
            const isActive = activePath.startsWith(item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`flex items-center gap-3 rounded-full px-4 py-2 text-sm font-medium transition hover:bg-white hover:text-[var(--color-brand)] ${
                  isActive ? "bg-[var(--color-brand)] text-white shadow-sm" : "text-[var(--color-text)]"
                }`}
              >
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="flex flex-col gap-4 border-b border-[var(--color-border)] bg-[#e8efc8] px-4 py-3 md:flex-row md:items-center md:justify-between md:px-6">
          <div className="flex flex-col gap-1">
            <p className="text-sm font-semibold text-[var(--color-text)]">Bienvenido, Administrador</p>
            <p className="text-xs text-[var(--color-muted)]">Gestiona tu plataforma desde aquí</p>
          </div>
          <div className="flex items-center gap-2 md:gap-3">
            <IconButton ariaLabel="Notificaciones">
              <BellIcon className="h-5 w-5" />
            </IconButton>
            <IconButton ariaLabel="Ayuda">
              <HelpIcon className="h-5 w-5" />
            </IconButton>
            <div className="flex items-center gap-2 rounded-full border border-[var(--color-border)] bg-white px-3 py-2 shadow-sm">
              <div className="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--color-brand)] text-sm font-semibold text-white">
                A
              </div>
              <div className="leading-tight">
                <p className="text-sm font-semibold text-[var(--color-text)]">Admin</p>
                <p className="text-xs text-[var(--color-muted)]">Administrador</p>
              </div>
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
    <nav className="flex items-center gap-2 overflow-x-auto border-b border-[var(--color-border)] bg-white px-4 py-2 md:hidden">
      {navItems.map((item) => {
        const isActive = activePath.startsWith(item.href);
        return (
          <Link
            key={item.href}
            href={item.href}
            className={`whitespace-nowrap rounded-full px-3 py-2 text-sm font-medium transition ${
              isActive
                ? "bg-[var(--color-brand)] text-white shadow-sm"
                : "border border-[var(--color-border)] text-[var(--color-text)] hover:border-[var(--color-brand)] hover:text-[var(--color-brand)]"
            }`}
          >
            {item.label}
          </Link>
        );
      })}
    </nav>
  );
}

function IconButton({ children, ariaLabel }: { children: ReactNode; ariaLabel: string }) {
  return (
    <button
      type="button"
      aria-label={ariaLabel}
      className="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border)] bg-white text-[var(--color-muted)] transition hover:text-[var(--color-brand)] hover:shadow-sm"
    >
      {children}
    </button>
  );
}

function BellIcon({ className }: { className?: string }) {
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
      <path d="M14 20a2 2 0 1 1-4 0" />
      <path d="M6 9a6 6 0 1 1 12 0v4.5l1.5 2.5H4.5L6 13.5Z" />
    </svg>
  );
}

function HelpIcon({ className }: { className?: string }) {
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
      <circle cx="12" cy="12" r="9" />
      <path d="M12 16v.01" />
      <path d="M10.5 9a1.5 1.5 0 0 1 3 0c0 .5-.25.9-.65 1.2l-.7.5c-.4.3-.65.7-.65 1.2V13" />
    </svg>
  );
}

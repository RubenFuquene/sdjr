"use client";

import { JSX, useState, type ChangeEvent } from "react";

type TabKey = "perfiles" | "proveedores" | "usuarios" | "administradores";

type DashboardTabsProps = {
  onTabChange?: (tab: TabKey) => void;
  onSearchChange?: (value: string) => void;
};

const tabs: { key: TabKey; label: string; icon: JSX.Element }[] = [
  { key: "perfiles", label: "Perfiles", icon: <UsersIcon className="h-4 w-4" /> },
  { key: "proveedores", label: "Proveedores", icon: <BuildingIcon className="h-4 w-4" /> },
  { key: "usuarios", label: "Usuarios", icon: <UserIcon className="h-4 w-4" /> },
  { key: "administradores", label: "Administradores", icon: <ShieldIcon className="h-4 w-4" /> },
];

export function DashboardTabs({ onTabChange, onSearchChange }: DashboardTabsProps) {
  const [active, setActive] = useState<TabKey>("perfiles");
  const [search, setSearch] = useState("");

  const handleTabClick = (tab: TabKey) => {
    setActive(tab);
    onTabChange?.(tab);
  };

  const handleSearch = (event: ChangeEvent<HTMLInputElement>) => {
    const value = event.target.value;
    setSearch(value);
    onSearchChange?.(value);
  };

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center gap-2">
        {tabs.map((tab) => {
          const isActive = tab.key === active;
          return (
            <button
              key={tab.key}
              type="button"
              onClick={() => handleTabClick(tab.key)}
              className={`inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-brand)] ${
                isActive
                  ? "bg-[var(--color-brand)] text-white shadow-sm"
                  : "border border-[var(--color-border)] bg-white text-[var(--color-text)] hover:border-[var(--color-brand)] hover:text-[var(--color-brand)]"
              }`}
              aria-pressed={isActive}
            >
              {tab.icon}
              <span>{tab.label}</span>
            </button>
          );
        })}
      </div>

      <div className="flex flex-col gap-3 rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <label className="flex flex-1 items-center gap-2 rounded-full border border-[var(--color-border)] bg-white px-3 py-2 text-sm text-[var(--color-text)] shadow-inner focus-within:border-[var(--color-brand)] focus-within:ring-2 focus-within:ring-[color:var(--color-brand)]/10">
          <SearchIcon className="h-4 w-4 text-[var(--color-muted)]" />
          <input
            value={search}
            onChange={handleSearch}
            type="text"
            placeholder="Buscar perfiles..."
            className="w-full bg-transparent text-sm outline-none placeholder:text-[var(--color-muted)]"
            aria-label="Buscar"
          />
        </label>
        <button
          type="button"
          className="inline-flex items-center gap-2 self-start rounded-full bg-[var(--color-brand)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--color-brand-600)] sm:self-auto"
        >
          <SearchIcon className="h-4 w-4" />
          Buscar
        </button>
      </div>
    </div>
  );
}

function SearchIcon({ className }: { className?: string }) {
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
      <circle cx="11" cy="11" r="6" />
      <path d="m15.5 15.5 3 3" />
    </svg>
  );
}

function UsersIcon({ className }: { className?: string }) {
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
      <path d="M16 13a4 4 0 1 0-8 0" />
      <circle cx="12" cy="7" r="3" />
      <path d="M5 19a5 5 0 0 1 14 0" />
    </svg>
  );
}

function BuildingIcon({ className }: { className?: string }) {
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
      <rect x="4" y="4" width="10" height="16" rx="2" />
      <path d="M14 9h3a1 1 0 0 1 1 1v10" />
      <path d="M8 8h2M8 12h2M8 16h2" />
    </svg>
  );
}

function UserIcon({ className }: { className?: string }) {
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
      <circle cx="12" cy="7" r="3" />
      <path d="M5 19a7 7 0 0 1 14 0" />
    </svg>
  );
}

function ShieldIcon({ className }: { className?: string }) {
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
      <path d="M12 21s7-3 7-9V6l-7-3-7 3v6c0 6 7 9 7 9Z" />
      <path d="M9.5 12 11 13.5 14.5 10" />
    </svg>
  );
}

import Link from "next/link";
import { Bell, ChevronLeft } from "lucide-react";

export default function AppNotificationsPage() {
  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center justify-between gap-3">
          <Link
            href="/app/discover"
            className="app-btn-icon app-header-back-button"
            aria-label="Volver a descubrir"
          >
            <ChevronLeft className="h-5 w-5" />
          </Link>

          <div className="flex-1">
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Notificaciones</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Centro de avisos de la app</p>
          </div>
        </div>
      </header>

      <div className="app-surface mt-4 p-6 text-center">
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]">
          <Bell className="h-6 w-6" />
        </div>

        <h2 className="mt-4 text-base text-[var(--color-app-text-dark)]">Funcionalidad en construccion</h2>
        <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">
          En este MVP aun no gestionamos notificaciones reales. Esta pantalla queda lista para integrar eventos en una
          fase posterior.
        </p>
      </div>
    </section>
  );
}

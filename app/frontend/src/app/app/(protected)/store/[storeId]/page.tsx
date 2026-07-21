import Link from "next/link";
import { AlertCircle, ArrowLeft, Clock, MapPin } from "lucide-react";
import { ApiError } from "@/lib/api/client";
import { getBranchDetail } from "@/lib/api/app-catalog";
import { mapBranchDetailToView, type BranchDetailView } from "@/types/app-catalog.adapters";

type StoreDetailPageProps = {
  params: Promise<{ storeId: string }>;
};

function NotFoundState() {
  return (
    <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
      <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
      <h1 className="text-lg text-[var(--color-app-text-dark)]">Tienda no encontrada</h1>
      <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
        Puede que ya no esté disponible o el enlace sea incorrecto.
      </p>
      <Link
        href="/app/discover"
        className="mt-2 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
      >
        Volver a Descubre
      </Link>
    </section>
  );
}

function ErrorState() {
  return (
    <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
      <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
      <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
        No se pudo cargar la tienda en este momento. Intenta de nuevo.
      </p>
    </section>
  );
}

export default async function StoreDetailPage({ params }: StoreDetailPageProps) {
  const { storeId } = await params;
  const branchId = Number.parseInt(storeId, 10);

  if (!Number.isInteger(branchId) || branchId <= 0) {
    return <NotFoundState />;
  }

  let store: BranchDetailView;

  try {
    const response = await getBranchDetail(branchId);
    store = mapBranchDetailToView(response.data);
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      return <NotFoundState />;
    }

    return <ErrorState />;
  }

  return (
    <section className="pb-6">
      <div className="relative h-52 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
        <Link
          href="/app/discover"
          className="app-btn-icon app-header-back-button bg-white/90 text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-button)]"
          aria-label="Volver"
        >
          <ArrowLeft className="h-5 w-5" />
        </Link>
      </div>

      <div className="space-y-4 px-4 pt-4">
        <header className="app-page-header p-4">
          {/* Nombre real del proveedor (SCRUM-289) como encabezado principal */}
          <h1 className="text-xl text-[var(--color-app-text-dark)]">{store.commerceName}</h1>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{store.branchName}</p>
        </header>

        <div className="app-surface p-4">
          <div className="space-y-3 text-sm text-[var(--color-app-text-secondary-purple)]">
            <div className="flex items-start gap-2">
              <MapPin className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <span>{store.address}</span>
            </div>
            <div className="flex items-start gap-2">
              <Clock className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <span>{store.scheduleLabel}</span>
            </div>
          </div>
        </div>

        {/*
          Sin sección de rating/reviews: no existe modelo de reseñas aún
          (SCRUM-350, post-MVP). Se omite en vez de fabricar un valor.
        */}

        <div className="app-surface p-4">
          {/*
            No enlaza a un producto específico: el id de esta página es de
            sucursal, no de producto (namespaces distintos en datos reales,
            a diferencia del mock donde coincidían). Listar los productos
            de esta sucursal es una funcionalidad nueva, fuera de alcance
            de esta fase (discover no filtra por sucursal hoy).
          */}
          <Link href="/app/discover" className="app-btn-primary flex items-center justify-center">
            Ver más cerca de ti
          </Link>
        </div>
      </div>
    </section>
  );
}

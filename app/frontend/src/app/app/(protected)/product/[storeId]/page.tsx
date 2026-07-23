import Image from "next/image";
import Link from "next/link";
import { AlertCircle, ArrowLeft, Clock3, Info, MapPin, Store } from "lucide-react";
import { ApiError } from "@/lib/api/client";
import { getBranchDetail, getProductDetail } from "@/lib/api/app-catalog";
import { getTodayPickupSchedule, mapProductDetailToView } from "@/types/app-catalog.adapters";
import type { BranchDetail } from "@/types/app-catalog";
import { ProductPurchasePanel } from "@/components/app/product/product-purchase-panel";

type ProductDetailPageProps = {
  params: Promise<{ storeId: string }>;
  searchParams: Promise<{ branchId?: string }>;
};

const URGENT_STOCK_THRESHOLD = 3;

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

/**
 * Etiqueta de cierre de la oferta (CA-01 de SCRUM-158), calculada al momento
 * del render del server component. Degradación honesta: sin expires_at no se
 * muestra nada (no se fabrica un horario).
 */
function buildExpiryLabel(expiresAt: string | null): string | null {
  if (!expiresAt) {
    return null;
  }

  const expiry = new Date(expiresAt);
  if (Number.isNaN(expiry.getTime())) {
    return null;
  }

  const remainingMs = expiry.getTime() - Date.now();
  if (remainingMs <= 0) {
    return "Oferta finalizada";
  }

  const totalMinutes = Math.floor(remainingMs / 60_000);
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;

  if (hours >= 48) {
    return `Termina el ${expiry.toLocaleDateString("es-CO", { day: "numeric", month: "short" })}`;
  }

  if (hours >= 1) {
    return `Termina en ${hours}h ${minutes}min`;
  }

  return `Termina en ${minutes}min`;
}

function NotFoundState() {
  return (
    <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
      <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
      <h1 className="text-lg text-[var(--color-app-text-dark)]">Producto no encontrado</h1>
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
        No se pudo cargar el producto en este momento. Intenta de nuevo.
      </p>
    </section>
  );
}

export default async function ProductDetailPage({ params, searchParams }: ProductDetailPageProps) {
  const { storeId } = await params;
  const { branchId: branchIdRaw } = await searchParams;
  const productId = Number.parseInt(storeId, 10);
  const parsedBranchId = branchIdRaw ? Number.parseInt(branchIdRaw, 10) : NaN;
  const branchId = Number.isInteger(parsedBranchId) && parsedBranchId > 0 ? parsedBranchId : null;

  if (!Number.isInteger(productId) || productId <= 0) {
    return <NotFoundState />;
  }

  // Producto y sucursal se piden en paralelo: la sucursal es informativa
  // (sección de recogida) y su fallo no debe bloquear ni retrasar el
  // producto, que es el dato principal de la página.
  const [productResult, branchResult] = await Promise.allSettled([
    getProductDetail(productId),
    branchId ? getBranchDetail(branchId) : Promise.resolve(null),
  ]);

  if (productResult.status === "rejected") {
    const error = productResult.reason;
    if (error instanceof ApiError && error.status === 404) {
      return <NotFoundState />;
    }

    return <ErrorState />;
  }

  const product = mapProductDetailToView(productResult.value.data);

  // Degradación honesta: sin branchId, o si la sucursal falla/no existe, la
  // sección de recogida simplemente se omite — nunca se fabrica un horario
  // o dirección.
  const branch: BranchDetail | null =
    branchResult.status === "fulfilled" && branchResult.value ? branchResult.value.data : null;

  const discount =
    product.originalPrice > product.price
      ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
      : 0;
  const expiryLabel = buildExpiryLabel(product.expiresAt);
  const isUrgentStock = product.quantityAvailable > 0 && product.quantityAvailable <= URGENT_STOCK_THRESHOLD;
  const pickupSchedule = getTodayPickupSchedule(branch?.hours);

  return (
    <section className="pb-6">
      <div className="relative h-56 overflow-hidden bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
        {product.photoUrl && (
          <Image
            src={product.photoUrl}
            alt={product.title}
            fill
            unoptimized
            className="object-cover"
            priority
          />
        )}

        <Link
          href="/app/discover"
          className="app-btn-icon app-header-back-button bg-white/90 text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-button)]"
          aria-label="Volver a Descubre"
        >
          <ArrowLeft className="h-5 w-5" />
        </Link>

        {discount > 0 && (
          <div className="absolute right-4 top-4 rounded-full bg-[var(--color-app-text-primary-purple)] px-3 py-1 text-xs text-white">
            -{discount}%
          </div>
        )}

        <div className="absolute bottom-4 left-4 flex flex-col items-start gap-2">
          {expiryLabel && (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs text-[var(--color-app-text-dark)]">
              <Clock3 className="h-3.5 w-3.5 text-[var(--color-app-tomatillo-medium)]" aria-hidden="true" />
              {expiryLabel}
            </span>
          )}
          <span
            className={`rounded-full px-3 py-1 text-xs ${
              isUrgentStock
                ? "bg-red-600 text-white"
                : "bg-white text-[var(--color-app-text-secondary-purple)]"
            }`}
          >
            {isUrgentStock ? `Solo quedan ${product.quantityAvailable}` : `Quedan ${product.quantityAvailable} disponibles`}
          </span>
        </div>
      </div>

      <div className="space-y-4 px-4 pt-4">
        <header className="app-page-header p-4">
          <h1 className="text-xl text-[var(--color-app-text-dark)]">{product.title}</h1>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
            {product.category} · {product.commerceName}
          </p>
        </header>

        <div className="app-surface p-4">
          <div className="flex items-end gap-2">
            <p className="text-3xl text-[var(--color-app-tomatillo-medium)]">{formatPrice(product.price)}</p>
            {product.originalPrice > product.price && (
              <p className="text-sm text-[var(--color-app-text-secondary-purple)] line-through">
                {formatPrice(product.originalPrice)}
              </p>
            )}
          </div>
          <p className="mt-1 text-sm text-[var(--color-app-text-secondary-purple)]">Por bolsa</p>
        </div>

        <div className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Descripción</h2>
          <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">{product.description}</p>
        </div>

        {branch && (
          <div className="app-surface p-4">
            <h2 className="text-base text-[var(--color-app-text-dark)]">Recoger en tienda</h2>
            <div className="mt-3 flex items-start gap-3">
              <Store className="mt-0.5 h-4 w-4 flex-shrink-0 text-[var(--color-app-text-primary-purple)]" />
              <div className="text-sm text-[var(--color-app-text-secondary-purple)]">
                <p className="text-[var(--color-app-text-dark)]">{branch.name}</p>
                {branch.address && (
                  <p className="mt-0.5 flex items-start gap-1">
                    <MapPin className="mt-0.5 h-3.5 w-3.5 flex-shrink-0" aria-hidden="true" />
                    {branch.address}
                  </p>
                )}
                <p className="mt-1">{pickupSchedule ?? "Consultar con el comercio"}</p>
              </div>
            </div>
          </div>
        )}

        <div className="app-surface-outlined flex gap-3 p-4">
          <Info className="mt-0.5 h-5 w-5 flex-shrink-0 text-[var(--color-app-text-primary-purple)]" aria-hidden="true" />
          <p className="text-sm text-[var(--color-app-text-dark)]">
            Esta es una bolsa sorpresa. El contenido puede variar según disponibilidad del comercio.
          </p>
        </div>

        <ProductPurchasePanel
          productId={product.id}
          branchId={branchId}
          price={product.price}
          maxQuantity={product.quantityAvailable}
        />
      </div>
    </section>
  );
}
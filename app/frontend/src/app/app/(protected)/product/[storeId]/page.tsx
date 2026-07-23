import Link from "next/link";
import { AlertCircle, ArrowLeft, Plus } from "lucide-react";
import { ApiError } from "@/lib/api/client";
import { getProductDetail } from "@/lib/api/app-catalog";
import { mapProductDetailToView, type ProductDetailView } from "@/types/app-catalog.adapters";

type ProductDetailPageProps = {
  params: Promise<{ storeId: string }>;
  searchParams: Promise<{ branchId?: string }>;
};

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

/**
 * Solo se pasa el id de producto y, si se conoce, el de sucursal — no datos
 * de negocio duplicados. El carrito hace su propio fetch con getProductDetail.
 * branchId es necesario porque un producto puede venderse en varias sucursales
 * y la orden requiere una específica (StoreOrderRequest exige commerce_branch_id).
 */
function buildCartHref(productId: number, branchId: number | null): string {
  const params = new URLSearchParams({ productId: String(productId) });

  if (branchId) {
    params.set("branchId", String(branchId));
  }

  return `/app/cart?${params.toString()}`;
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

  let product: ProductDetailView;

  try {
    const response = await getProductDetail(productId);
    product = mapProductDetailToView(response.data);
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      return <NotFoundState />;
    }

    return <ErrorState />;
  }

  const discount =
    product.originalPrice > product.price
      ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
      : 0;

  return (
    <section className="pb-6">
      <div className="relative h-56 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
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

        <div className="absolute bottom-4 left-4 rounded-full bg-white px-3 py-1 text-xs text-[var(--color-app-text-secondary-purple)]">
          Quedan {product.quantityAvailable} disponibles
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

        <div className="app-surface p-4">
          <div className="flex items-center justify-between gap-3">
            <div>
              <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Total estimado</p>
              <p className="text-2xl text-[var(--color-app-text-dark)]">{formatPrice(product.price)}</p>
            </div>

            <Link href={buildCartHref(product.id, branchId)} className="app-btn-primary gap-2">
              <Plus className="h-4 w-4" />
              Agregar al carrito
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}

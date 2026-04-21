import Link from "next/link";
import { ArrowLeft, Plus, Store, Truck } from "lucide-react";
import { getStoreById } from "@/lib/app/mock-catalog";

type ProductDetailPageProps = {
  params: Promise<{ storeId: string }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
};

type ProductDetailView = {
  id: number;
  name: string;
  category: string;
  address: string;
  price: number;
  originalPrice: number;
  available: number;
  pickupTime: string;
  deliveryTime: string;
  deliveryCost: number;
  description: string;
  source: "discover" | "default";
};

function firstParam(value: string | string[] | undefined): string | undefined {
  if (Array.isArray(value)) {
    return value[0];
  }

  return value;
}

function toNumber(value: string | undefined, fallback: number): number {
  if (!value) {
    return fallback;
  }

  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

function toText(value: string | undefined, fallback: string): string {
  if (!value) {
    return fallback;
  }

  const trimmed = value.trim();
  return trimmed.length > 0 ? trimmed : fallback;
}

function buildProductFromSearchParams(
  searchParams: Record<string, string | string[] | undefined>,
  productId: number
): ProductDetailView | null {
  const source = firstParam(searchParams.source);
  if (source !== "discover") {
    return null;
  }

  return {
    id: productId,
    name: toText(firstParam(searchParams.name), "Producto cercano"),
    category: toText(firstParam(searchParams.category), "Sin categoria"),
    address: toText(firstParam(searchParams.address), "Ubicacion no disponible"),
    price: toNumber(firstParam(searchParams.price), 0),
    originalPrice: toNumber(firstParam(searchParams.originalPrice), toNumber(firstParam(searchParams.price), 0)),
    available: toNumber(firstParam(searchParams.available), 1),
    pickupTime: toText(firstParam(searchParams.pickupTime), "Consultar con el comercio"),
    deliveryTime: toText(firstParam(searchParams.deliveryTime), "Consultar disponibilidad"),
    deliveryCost: toNumber(firstParam(searchParams.deliveryCost), 0),
    description: toText(firstParam(searchParams.description), "Producto cercano segun tu ubicacion."),
    source: "discover",
  };
}

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

function buildCartHref(product: ProductDetailView, storeId: number): string {
  const params = new URLSearchParams({
    source: "product-detail",
    storeId: String(storeId),
    productId: String(product.id),
    name: product.name,
    category: product.category,
    address: product.address,
    price: String(product.price),
    originalPrice: String(product.originalPrice),
    available: String(product.available),
    pickupTime: product.pickupTime,
    deliveryTime: product.deliveryTime,
    deliveryCost: String(product.deliveryCost),
    description: product.description,
  });

  return `/app/cart?${params.toString()}`;
}

export default async function ProductDetailPage({ params, searchParams }: ProductDetailPageProps) {
  const { storeId } = await params;
  const resolvedSearchParams = await searchParams;
  const parsedStoreId = Number.parseInt(storeId, 10);

  const safeStoreId = Number.isNaN(parsedStoreId) ? 1 : parsedStoreId;
  const fallbackStore = getStoreById(safeStoreId) ?? getStoreById(1);
  const productFromQuery = buildProductFromSearchParams(resolvedSearchParams, safeStoreId);

  const product: ProductDetailView | null = productFromQuery
    ?? (fallbackStore
      ? {
          id: fallbackStore.id,
          name: fallbackStore.name,
          category: fallbackStore.category,
          address: fallbackStore.address,
          price: fallbackStore.price,
          originalPrice: fallbackStore.originalPrice,
          available: fallbackStore.available,
          pickupTime: fallbackStore.pickupTime,
          deliveryTime: fallbackStore.deliveryTime,
          deliveryCost: fallbackStore.deliveryCost,
          description: fallbackStore.description,
          source: "default",
        }
      : null);

  if (!product) {
    return null;
  }

  const discount = Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100);
  const backHref = product.source === "discover" ? "/app/discover" : `/app/store/${product.id}`;
  const cartStoreId = fallbackStore?.id ?? 1;

  return (
    <section className="pb-6">
      <div className="relative h-56 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
        <Link
          href={backHref}
          className="app-btn-icon app-header-back-button bg-white/90 text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-button)]"
          aria-label="Volver a tienda"
        >
          <ArrowLeft className="h-5 w-5" />
        </Link>

        <div className="absolute right-4 top-4 rounded-full bg-[var(--color-app-text-primary-purple)] px-3 py-1 text-xs text-white">
          -{discount}%
        </div>

        <div className="absolute bottom-4 left-4 rounded-full bg-white px-3 py-1 text-xs text-[var(--color-app-text-secondary-purple)]">
          Quedan {product.available} disponibles
        </div>
      </div>

      <div className="space-y-4 px-4 pt-4">
        <header className="app-page-header p-4">
          <h1 className="text-xl text-[var(--color-app-text-dark)]">Bolsa sorpresa de {product.category.toLowerCase()}</h1>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{product.name}</p>
        </header>

        <div className="app-surface p-4">
          <div className="flex items-end gap-2">
            <p className="text-3xl text-[var(--color-app-tomatillo-medium)]">{formatPrice(product.price)}</p>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)] line-through">
              {formatPrice(product.originalPrice)}
            </p>
          </div>
          <p className="mt-1 text-sm text-[var(--color-app-text-secondary-purple)]">Por bolsa</p>
        </div>

        <div className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Metodo de entrega</h2>

          <div className="mt-3 space-y-2">
            <div className="flex items-start gap-3 rounded-xl border border-[var(--color-app-ui-divider)] p-3">
              <Store className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <div>
                <p className="text-sm text-[var(--color-app-text-dark)]">Recoger en tienda</p>
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{product.pickupTime}</p>
              </div>
            </div>

            <div className="flex items-start gap-3 rounded-xl border border-[var(--color-app-ui-divider)] p-3">
              <Truck className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <div>
                <p className="text-sm text-[var(--color-app-text-dark)]">Envio a domicilio</p>
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">
                  {product.deliveryTime} · {formatPrice(product.deliveryCost)}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div className="app-surface p-4">
          <div className="flex items-center justify-between gap-3">
            <div>
              <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Total estimado</p>
              <p className="text-2xl text-[var(--color-app-text-dark)]">{formatPrice(product.price)}</p>
            </div>

            <Link
              href={buildCartHref(product, cartStoreId)}
              className="app-btn-primary gap-2"
            >
              <Plus className="h-4 w-4" />
              Agregar al carrito
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}

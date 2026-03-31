import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, Plus, Store, Truck } from "lucide-react";
import { getStoreById } from "@/lib/app/mock-catalog";

type ProductDetailPageProps = {
  params: Promise<{ storeId: string }>;
};

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export default async function ProductDetailPage({ params }: ProductDetailPageProps) {
  const { storeId } = await params;
  const parsedStoreId = Number.parseInt(storeId, 10);

  if (Number.isNaN(parsedStoreId)) {
    notFound();
  }

  const store = getStoreById(parsedStoreId);
  if (!store) {
    notFound();
  }

  const discount = Math.round(((store.originalPrice - store.price) / store.originalPrice) * 100);

  return (
    <section className="pb-6">
      <div className="relative h-56 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
        <Link
          href={`/app/store/${store.id}`}
          className="app-btn-icon app-header-back-button bg-white/90 text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-button)]"
          aria-label="Volver a tienda"
        >
          <ArrowLeft className="h-5 w-5" />
        </Link>

        <div className="absolute right-4 top-4 rounded-full bg-[var(--color-app-text-primary-purple)] px-3 py-1 text-xs text-white">
          -{discount}%
        </div>

        <div className="absolute bottom-4 left-4 rounded-full bg-white px-3 py-1 text-xs text-[var(--color-app-text-secondary-purple)]">
          Quedan {store.available} disponibles
        </div>
      </div>

      <div className="space-y-4 px-4 pt-4">
        <header className="app-page-header p-4">
          <h1 className="text-xl text-[var(--color-app-text-dark)]">Bolsa sorpresa de {store.category.toLowerCase()}</h1>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{store.name}</p>
        </header>

        <div className="app-surface p-4">
          <div className="flex items-end gap-2">
            <p className="text-3xl text-[var(--color-app-tomatillo-medium)]">{formatPrice(store.price)}</p>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)] line-through">
              {formatPrice(store.originalPrice)}
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
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{store.pickupTime}</p>
              </div>
            </div>

            <div className="flex items-start gap-3 rounded-xl border border-[var(--color-app-ui-divider)] p-3">
              <Truck className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <div>
                <p className="text-sm text-[var(--color-app-text-dark)]">Envio a domicilio</p>
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">
                  {store.deliveryTime} · {formatPrice(store.deliveryCost)}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div className="app-surface p-4">
          <div className="flex items-center justify-between gap-3">
            <div>
              <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Total estimado</p>
              <p className="text-2xl text-[var(--color-app-text-dark)]">{formatPrice(store.price)}</p>
            </div>

            <Link
              href={`/app/cart?storeId=${store.id}`}
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

import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, Clock, Info, MapPin, Star } from "lucide-react";
import { getStoreById } from "@/lib/app/mock-catalog";

type StoreDetailPageProps = {
  params: Promise<{ storeId: string }>;
};

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export default async function StoreDetailPage({ params }: StoreDetailPageProps) {
  const { storeId } = await params;
  const parsedStoreId = Number.parseInt(storeId, 10);

  if (Number.isNaN(parsedStoreId)) {
    notFound();
  }

  const store = getStoreById(parsedStoreId);
  if (!store) {
    notFound();
  }

  const savings = store.originalPrice - store.price;

  return (
    <section className="pb-6">
      <div className="relative h-52 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)] px-4 pt-4">
        <Link
          href="/app/discover"
          className="inline-flex rounded-full bg-white/90 p-2 text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-button)]"
          aria-label="Volver"
        >
          <ArrowLeft className="h-5 w-5" />
        </Link>

        <div className="absolute bottom-4 right-4 flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs text-[var(--color-app-text-dark)]">
          <Star className="h-3.5 w-3.5 fill-[#F59E0B] text-[#F59E0B]" />
          <span>{store.rating}</span>
          <span className="text-[var(--color-app-text-secondary-purple)]">({store.reviews})</span>
        </div>
      </div>

      <div className="space-y-4 px-4 pt-4">
        <header className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <h1 className="text-xl text-[var(--color-app-text-dark)]">{store.name}</h1>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{store.category}</p>
        </header>

        <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <div className="space-y-3 text-sm text-[var(--color-app-text-secondary-purple)]">
            <div className="flex items-start gap-2">
              <MapPin className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <span>{store.address}</span>
            </div>
            <div className="flex items-start gap-2">
              <Clock className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
              <span>{store.pickupTime}</span>
            </div>
          </div>

          <div className="mt-4 rounded-xl bg-[var(--color-app-tomatillo-soft)] p-3 text-sm text-[var(--color-app-text-primary-purple)]">
            <div className="flex items-start gap-2">
              <Info className="mt-0.5 h-4 w-4" />
              <p>
                Recoge antes del cierre. Quedan <strong>{store.available}</strong> bolsas disponibles.
              </p>
            </div>
          </div>
        </div>

        <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Que incluye</h2>
          <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">{store.description}</p>
        </div>

        <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <div className="flex items-end justify-between gap-4">
            <div>
              <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Precio</p>
              <div className="mt-1 flex items-end gap-2">
                <p className="text-2xl text-[var(--color-app-text-primary-purple)]">{formatPrice(store.price)}</p>
                <p className="text-sm text-[var(--color-app-text-secondary-purple)] line-through">
                  {formatPrice(store.originalPrice)}
                </p>
              </div>
              <p className="text-xs text-[var(--color-app-status-success)]">Ahorras {formatPrice(savings)}</p>
            </div>

            <Link
              href={`/app/product/${store.id}`}
              className="inline-flex h-11 items-center rounded-xl bg-[var(--color-app-text-primary-purple)] px-4 text-sm text-white"
            >
              Rescatar ahora
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}

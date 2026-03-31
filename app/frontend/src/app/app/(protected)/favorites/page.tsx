import { ChevronRight, Heart, MapPin, Star } from "lucide-react";
import Link from "next/link";

type FavoriteStore = {
  id: number;
  name: string;
  category: string;
  distance: string;
  address: string;
  rating: number;
  minPrice: number;
  orderCount: number;
  lastOrderLabel: string;
};

const FAVORITE_STORES: FavoriteStore[] = [
  {
    id: 1,
    name: "Panaderia El Trigal",
    category: "Panaderia",
    distance: "1.2 km",
    address: "Calle 72 #10-34, Chapinero",
    rating: 4.8,
    minPrice: 8000,
    orderCount: 12,
    lastOrderLabel: "Hace 2 dias",
  },
  {
    id: 2,
    name: "Cafe Amor Perfecto",
    category: "Cafeteria",
    distance: "0.8 km",
    address: "Cra 7 #45-23, Chapinero",
    rating: 4.9,
    minPrice: 6000,
    orderCount: 8,
    lastOrderLabel: "Hace 5 dias",
  },
  {
    id: 3,
    name: "Restaurante Sabor Local",
    category: "Comida rapida",
    distance: "2.1 km",
    address: "Calle 85 #15-40, Usaquen",
    rating: 4.7,
    minPrice: 12000,
    orderCount: 6,
    lastOrderLabel: "Hace 1 semana",
  },
];

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export default function AppFavoritesPage() {
  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-app-tomatillo-soft)]">
            <Heart className="h-5 w-5 fill-[var(--color-app-text-primary-purple)] text-[var(--color-app-text-primary-purple)]" />
          </div>
          <div>
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Favoritos</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Tus comercios frecuentes</p>
          </div>
        </div>
      </header>

      <div className="mt-4 space-y-4">
        {FAVORITE_STORES.map((store, index) => (
          <article
            key={store.id}
            className="app-surface overflow-hidden"
          >
            <div className="relative h-36 bg-gradient-to-br from-[var(--color-app-tomatillo-soft)] via-white to-[var(--color-app-ui-background-soft)]">
              <div className="absolute left-3 top-3 rounded-full bg-[var(--color-app-text-primary-purple)] px-3 py-1 text-xs text-white">
                {store.orderCount} pedidos
              </div>

              <div className="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-white px-2 py-1 text-xs text-[var(--color-app-text-dark)]">
                <Star className="h-3.5 w-3.5 fill-[#F59E0B] text-[#F59E0B]" />
                <span>{store.rating}</span>
              </div>

              <div className="absolute bottom-3 left-3 rounded-lg bg-white px-2 py-1 text-xs text-[var(--color-app-text-secondary-purple)]">
                Comercio favorito {index + 1}
              </div>
            </div>

            <div className="space-y-3 px-4 py-4">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <h2 className="text-base text-[var(--color-app-text-dark)]">{store.name}</h2>
                  <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{store.category}</p>
                </div>
                <div className="text-right">
                  <p className="text-lg text-[var(--color-app-tomatillo-medium)]">{formatPrice(store.minPrice)}</p>
                  <p className="text-xs text-[var(--color-app-text-secondary-purple)]">desde</p>
                </div>
              </div>

              <div className="flex items-center gap-2 text-xs text-[var(--color-app-text-secondary-purple)]">
                <MapPin className="h-3.5 w-3.5" />
                <span>{store.distance}</span>
                <span>•</span>
                <span>{store.lastOrderLabel}</span>
              </div>

              <div className="app-surface-soft px-3 py-2 text-sm text-[var(--color-app-text-secondary-purple)]">
                {store.address}
              </div>

              <div className="flex gap-2">
                <Link
                  href={`/app/product/${store.id}`}
                  className="app-btn-primary flex-1"
                >
                  Pedir de nuevo
                </Link>
                <Link
                  href={`/app/store/${store.id}`}
                  className="app-btn-icon flex h-[52px] w-[52px] items-center justify-center bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]"
                  aria-label={`Ver detalles de ${store.name}`}
                >
                  <ChevronRight className="h-5 w-5" />
                </Link>
              </div>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}

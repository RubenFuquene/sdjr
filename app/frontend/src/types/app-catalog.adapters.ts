import type { BranchDetail, NearbyBranch, NearbyProduct, NearbyProductsResponse, ProductDetail } from "./app-catalog";

export interface DiscoverNearbyCard {
  productId: number;
  branchId: number | null;
  name: string;
  category: string;
  address: string;
  price: number;
  originalPrice: number;
  available: number;
  distanceKm: number;
  imageUrl?: string | null;
  /** Horario de recogida de hoy en la sucursal asignada, o null si no hay dato real. */
  pickupSchedule: string | null;
}

/**
 * Resuelve el horario de recogida de HOY para la sucursal asignada al producto.
 * day_of_week: 0=Domingo, 6=Sábado (mismo criterio que Date.getDay()).
 * Sin dato real → null (la UI decide el estado neutro, nunca se fabrica un horario).
 */
function getTodayPickupSchedule(branch: NearbyBranch | null): string | null {
  if (!branch?.hours || branch.hours.length === 0) {
    return null;
  }

  const todayIndex = new Date().getDay();
  const todayHours = branch.hours.find((hour) => hour.day_of_week === todayIndex);

  if (!todayHours) {
    return null;
  }

  return `Hoy ${todayHours.open_time.slice(0, 5)} - ${todayHours.close_time.slice(0, 5)}`;
}

export interface DiscoverMapPin {
  id: string;
  branchId: number;
  lat: number;
  lng: number;
  title: string;
  subtitle: string;
  distanceKm: number;
  minPrice: number;
  maxPrice: number;
  productCount: number;
  productIds: number[];
}

function toNumber(value: unknown, fallback = 0): number {
  if (typeof value === "number" && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === "string") {
    const parsed = Number(value);
    if (Number.isFinite(parsed)) {
      return parsed;
    }
  }

  return fallback;
}

function toText(value: unknown, fallback: string): string {
  if (typeof value !== "string") {
    return fallback;
  }

  const trimmed = value.trim();
  return trimmed.length > 0 ? trimmed : fallback;
}

function isValidCoordinates(lat: number, lng: number): boolean {
  return Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
}

function getProductCategoryLabel(product: NearbyProduct): string {
  return toText(product.category, "Sin categoría");
}

function getProductPrice(product: NearbyProduct): number {
  if (product.discounted_price !== undefined && product.discounted_price !== null) {
    return toNumber(product.discounted_price);
  }

  if (product.price !== undefined && product.price !== null) {
    return toNumber(product.price);
  }

  return toNumber(product.original_price, 0);
}

function getProductImageUrl(product: NearbyProduct): string | null {
  const extendedProduct = product as NearbyProduct & {
    image_url?: unknown;
    photo_url?: unknown;
    main_photo_url?: unknown;
    cover_image?: unknown;
    photos?: unknown;
  };

  const directCandidates = [
    extendedProduct.image_url,
    extendedProduct.photo_url,
    extendedProduct.main_photo_url,
    extendedProduct.cover_image,
  ];

  for (const candidate of directCandidates) {
    if (typeof candidate === "string" && candidate.trim().length > 0) {
      return candidate.trim();
    }
  }

  if (Array.isArray(extendedProduct.photos)) {
    for (const photo of extendedProduct.photos) {
      if (typeof photo === "string" && photo.trim().length > 0) {
        return photo.trim();
      }

      if (photo && typeof photo === "object") {
        const urlCandidate = (photo as { url?: unknown; path?: unknown; image_url?: unknown }).url
          ?? (photo as { url?: unknown; path?: unknown; image_url?: unknown }).path
          ?? (photo as { url?: unknown; path?: unknown; image_url?: unknown }).image_url;

        if (typeof urlCandidate === "string" && urlCandidate.trim().length > 0) {
          return urlCandidate.trim();
        }
      }
    }
  }

  return null;
}

export function mapNearbyProductToDiscoverCard(product: NearbyProduct): DiscoverNearbyCard {
  const nearestBranch = product.nearest_branch;
  const distanceKm = toNumber(product.nearest_branch_distance_km);
  const displayPrice =
    product.discounted_price !== undefined
      ? toNumber(product.discounted_price)
      : toNumber(product.price);
  const originalPrice =
    product.original_price !== undefined
      ? toNumber(product.original_price)
      : displayPrice;

  return {
    productId: product.id,
    branchId: nearestBranch?.id ?? null,
    name: toText(nearestBranch?.commerce_name ?? product.commerce_name ?? nearestBranch?.name ?? product.name ?? product.title, "Comercio cercano"),
    category: getProductCategoryLabel(product),
    address: toText(nearestBranch?.address, "Ubicacion no disponible"),
    price: displayPrice,
    originalPrice,
    available: toNumber(product.quantity_available, 1),
    distanceKm,
    imageUrl: getProductImageUrl(product),
    pickupSchedule: getTodayPickupSchedule(nearestBranch),
  };
}

export function mapNearbyProductsToDiscoverCards(
  source: NearbyProductsResponse | NearbyProduct[]
): DiscoverNearbyCard[] {
  const products = Array.isArray(source) ? source : source.data;
  return products.map(mapNearbyProductToDiscoverCard);
}

/**
 * Decision MVP: un pin por sucursal para evitar ruido visual en el mapa.
 */
export function mapNearbyProductsToMapPins(
  source: NearbyProductsResponse | NearbyProduct[]
): DiscoverMapPin[] {
  const products = Array.isArray(source) ? source : source.data;
  const pinsByBranch = new Map<number, DiscoverMapPin>();

  for (const product of products) {
    const nearestBranch = product.nearest_branch;

    if (!nearestBranch) {
      continue;
    }

    const branchId = toNumber(nearestBranch.id, 0);
    const lat = toNumber(nearestBranch.latitude, NaN);
    const lng = toNumber(nearestBranch.longitude, NaN);

    if (branchId <= 0 || !isValidCoordinates(lat, lng)) {
      continue;
    }

    const price = getProductPrice(product);
    const distanceKm = toNumber(product.nearest_branch_distance_km ?? nearestBranch.distance_km, 0);
    const branchName = toText(nearestBranch.name, "Sucursal cercana");
    const subtitle = toText(nearestBranch.address, getProductCategoryLabel(product));

    const existing = pinsByBranch.get(branchId);

    if (!existing) {
      pinsByBranch.set(branchId, {
        id: `branch-${branchId}`,
        branchId,
        lat,
        lng,
        title: branchName,
        subtitle,
        distanceKm,
        minPrice: price,
        maxPrice: price,
        productCount: 1,
        productIds: [product.id],
      });
      continue;
    }

    existing.minPrice = Math.min(existing.minPrice, price);
    existing.maxPrice = Math.max(existing.maxPrice, price);
    existing.productCount += 1;
    existing.productIds.push(product.id);
    existing.distanceKm = Math.min(existing.distanceKm, distanceKm);
  }

  return Array.from(pinsByBranch.values()).sort((a, b) => a.distanceKm - b.distanceKm);
}

// ============================================
// Detalle de producto (GET /catalog/products/{id})
// ============================================

export interface ProductDetailView {
  id: number;
  title: string;
  category: string;
  description: string;
  commerceName: string;
  price: number;
  originalPrice: number;
  quantityAvailable: number;
  photoUrl: string | null;
}

/**
 * Degradación honesta: ante dato ausente se muestra un estado neutro real
 * ("Sin categoría", "Sin descripción disponible."), nunca un valor del prototipo.
 */
export function mapProductDetailToView(detail: ProductDetail): ProductDetailView {
  const price = detail.discounted_price ?? detail.original_price;

  return {
    id: detail.id,
    title: toText(detail.title, "Producto"),
    category: toText(detail.category, "Sin categoría"),
    description: toText(detail.description, "Sin descripción disponible."),
    commerceName: toText(detail.commerce_name, "Proveedor"),
    price: toNumber(price),
    originalPrice: toNumber(detail.original_price, price),
    quantityAvailable: toNumber(detail.quantity_available, 0),
    photoUrl: detail.photos?.[0]?.presigned_url ?? null,
  };
}

// ============================================
// Detalle de tienda/sucursal (GET /catalog/commerce-branches/{id})
// ============================================

export interface BranchDetailView {
  id: number;
  commerceName: string;
  branchName: string;
  address: string;
  scheduleLabel: string;
  photoUrl: string | null;
}

/**
 * Sin rating/reviews: no existe modelo de reseñas aún (SCRUM-350, post-MVP).
 * Se omite la sección en vez de fabricar un valor, siguiendo la decisión
 * de degradación honesta acordada para esta fase.
 */
export function mapBranchDetailToView(detail: BranchDetail): BranchDetailView {
  const hasHours = Array.isArray(detail.hours) && detail.hours.length > 0;

  return {
    id: detail.id,
    commerceName: toText(detail.commerce_name, "Proveedor"),
    branchName: toText(detail.name, "Sucursal"),
    address: toText(detail.address, "Ubicación no disponible"),
    scheduleLabel: hasHours
      ? `${detail.hours![0].open_time} - ${detail.hours![0].close_time}`
      : "Consultar con el comercio",
    photoUrl: detail.photos?.[0]?.presigned_url ?? null,
  };
}

import type { AppStoreCatalogItem } from "@/lib/app/mock-catalog";
import type { NearbyProduct, NearbyProductsResponse } from "./app-catalog";

export interface DiscoverNearbyCard extends AppStoreCatalogItem {
  productId: number;
  branchId: number | null;
  distanceKm: number;
  imageUrl?: string | null;
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
  const categoryId = toNumber(product.product_category_id ?? product.category_id, 0);
  return categoryId > 0 ? `Categoria ${categoryId}` : "Sin categoria";
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
    id: nearestBranch?.id ?? product.id,
    name: toText(nearestBranch?.commerce_name ?? nearestBranch?.name ?? product.name ?? product.title, "Comercio cercano"),
    category: product.category_id ? `Categoria ${product.category_id}` : "Sin categoria",
    address: toText(nearestBranch?.address, "Ubicacion no disponible"),
    rating: 0,
    reviews: 0,
    price: displayPrice,
    originalPrice,
    available: toNumber(product.quantity_available, 1),
    pickupTime: "Consultar con el comercio",
    deliveryTime: "Consultar disponibilidad",
    deliveryCost: 0,
    description: toText(product.description, "Producto cercano segun tu ubicacion."),
    productId: product.id,
    branchId: nearestBranch?.id ?? null,
    distanceKm,
    imageUrl: getProductImageUrl(product),
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

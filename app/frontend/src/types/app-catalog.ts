export interface NearbyProductsParams {
  latitude: number;
  longitude: number;
  radius?: number;
  categoryId?: number;
  maxPrice?: number;
  perPage?: number;
  page?: number;
}

/** day_of_week: 0=Domingo, 6=Sábado (mismo criterio que Date.getDay() en JS) */
export interface NearbyBranchHour {
  id: number;
  day_of_week: number;
  open_time: string;
  close_time: string;
  note?: string | null;
}

export interface NearbyBranch {
  id: number;
  name: string;
  address?: string | null;
  latitude: number | null;
  longitude: number | null;
  distance_km?: number | null;
  commerce_name?: string | null;
  hours?: NearbyBranchHour[];
}

export interface NearbyProduct {
  id: number;
  name?: string;
  title?: string;
  price?: number;
  discounted_price?: number;
  original_price?: number;
  quantity_total?: number;
  quantity_available?: number;
  description?: string | null;
  category_id?: number | null;
  product_category_id?: number | null;
  /** Nombre real de la categoría (ProductResource::category, whenLoaded). Ausente si la relación no se cargó. */
  category?: string | null;
  /** Nombre real del comercio propietario (ProductResource::commerce_name, whenLoaded). */
  commerce_name?: string | null;
  nearest_branch_distance_km: number;
  nearest_branch: NearbyBranch | null;
  commerce_branches?: NearbyBranch[];
}

/**
 * Detalle de producto: GET /api/v1/catalog/products/{id}
 * Shape de ProductResource (backend), solo items activos.
 */
export interface ProductDetail {
  id: number;
  commerce_id: number;
  commerce_name?: string | null;
  product_category_id: number | null;
  category?: string | null;
  title: string;
  description: string | null;
  product_type: "single" | "package";
  original_price: number;
  discounted_price: number | null;
  quantity_total: number;
  quantity_available: number;
  available_for_packaging?: number | null;
  expires_at?: string | null;
  photos?: Array<{ id: number; presigned_url?: string | null; file_path?: string }>;
  status: string;
  created_at?: string;
  updated_at?: string;
}

export interface ProductDetailResponse {
  status: boolean;
  message: string | null;
  data: ProductDetail;
}

/**
 * Detalle de sucursal/tienda: GET /api/v1/catalog/commerce-branches/{id}
 * Shape de CommerceBranchResource (backend), solo sucursales activas.
 */
export interface BranchDetail {
  id: number;
  commerce_id: number;
  commerce_name?: string | null;
  name: string;
  address?: string | null;
  department?: string | null;
  city?: string | null;
  neighborhood?: string | null;
  latitude: number | null;
  longitude: number | null;
  phone?: string | null;
  email?: string | null;
  is_active?: boolean | null;
  hours?: NearbyBranchHour[];
  photos?: Array<{ id: number; presigned_url?: string | null; file_path?: string }>;
  created_at?: string;
  updated_at?: string;
}

export interface BranchDetailResponse {
  status: boolean;
  message: string | null;
  data: BranchDetail;
}

export interface NearbyProductsResponse {
  data: NearbyProduct[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  message?: string;
  status?: boolean;
}

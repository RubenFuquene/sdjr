export interface NearbyProductsParams {
  latitude: number;
  longitude: number;
  radius?: number;
  categoryId?: number;
  maxPrice?: number;
  perPage?: number;
  page?: number;
}

export interface NearbyBranch {
  id: number;
  name: string;
  address?: string | null;
  latitude: number | null;
  longitude: number | null;
  distance_km?: number | null;
  commerce_name?: string | null;
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
  nearest_branch_distance_km: number;
  nearest_branch: NearbyBranch | null;
  commerce_branches?: NearbyBranch[];
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

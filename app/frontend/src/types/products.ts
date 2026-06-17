export type ProductType = "single" | "package";

export interface ProductPackageItemInput {
  productId: number;
  quantity: number;
}

export interface ProductPhotoFromAPI {
  id?: number;
  product_id?: number;
  commerce_id?: number;
  document_type?: string;
  file_path: string;
  presigned_url?: string;
  upload_status?: string;
  s3_etag?: string | null;
  s3_object_size?: number | null;
  s3_last_modified?: string | null;
  version_number?: number | null;
  expires_at?: string | null;
  uploaded_by_id?: number | null;
  failed_attempts?: number | null;
  mime_type?: string | null;
  verified?: boolean | null;
  uploaded_at?: string | null;
  verified_at?: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface ProductFromAPI {
  id: number;
  commerce_id: number;
  product_category_id: number;
  title: string;
  description: string | null;
  product_type: ProductType;
  original_price: number;
  discounted_price: number | null;
  quantity_total: number;
  quantity_available: number;
  available_for_packaging?: number;
  expires_at: string | null;
  photos?: ProductPhotoFromAPI[];
  status: string;
  created_at: string;
  updated_at: string;
}

export interface ProductCategoryFromAPI {
  id: number;
  name: string;
  description: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface ProductFormInput {
  commerceId: number;
  productCategoryId: number;
  title: string;
  description?: string | null;
  productType: ProductType;
  originalPrice: number;
  discountedPrice?: number | null;
  quantityTotal?: number;
  quantityAvailable: number;
  expiresAt?: string | null;
  status?: string;
  branchId?: number | null;
  packageItems?: ProductPackageItemInput[];
  photos?: Array<{
    file_name: string;
    mime_type: string;
    file_size_bytes: number;
    versioning_enabled?: string;
    metadata?: Record<string, unknown>;
  }>;
}

export type ProviderProductFormFieldErrors = Record<string, string>;

export type ProviderProductFormInput = Omit<ProductFormInput, "commerceId"> & {
  commerceId?: number;
};
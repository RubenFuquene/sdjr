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

export interface ProductCommerceBranchFromAPI {
  id: number;
  name: string;
  /** Ausente en respuestas que no cargan el pivote (ej. package_items anidados). SCRUM-277.
   * Para product_type=single: unidades disponibles en esta sede.
   * Para product_type=package: packs comprometidos en esta sede (SCRUM-361). */
  quantity_available?: number;
  is_published?: boolean;
  /** Solo packs: cuándo se ajustó solo el compromiso por falta de stock de un componente (SCRUM-361). */
  auto_adjusted_at?: string | null;
  /** Solo packs: cantidad comprometida antes del ajuste automático. */
  auto_adjusted_from?: number | null;
  /** Solo product_type=single, solo presente cuando el backend lo precalculó
   * (listado del proveedor): stock libre para comprometer en packs en esta sede. */
  available_for_packaging?: number | null;
}

/** Asignación de un producto individual a una sede, con su inventario y publicación (SCRUM-277 Fase 1). */
export interface ProductBranchAssignment {
  branchId: number;
  quantityAvailable: number;
  isPublished: boolean;
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
  expires_at: string | null;
  photos?: ProductPhotoFromAPI[];
  /** Solo presente si el backend cargó la relación (whenLoaded); ausente en algunos listados. */
  commerce_branches?: ProductCommerceBranchFromAPI[];
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
  expiresAt?: string | null;
  status?: string;
  /**
   * SCRUM-277/361: la asignación multi-sede con inventario y publicación,
   * para ambos tipos de producto. Para product_type=single, quantityAvailable
   * es stock físico; para product_type=package, es el compromiso de packs en
   * esa sede — el mismo campo, dos lecturas según el tipo (SCRUM-361).
   */
  branches?: ProductBranchAssignment[];
  packageItems?: ProductPackageItemInput[];
  photos?: Array<{
    file_name: string;
    mime_type: string;
    file_size_bytes: number;
    versioning_enabled?: string;
    metadata?: Record<string, unknown>;
  }>;
  /**
   * SCRUM-361, Tarea 3.3-3.4: confirma aplicar el ajuste automático a los
   * packs afectados por bajar el stock de un componente. Sin esto, un
   * cambio con impacto responde 409 en vez de aplicarse.
   */
  confirmPackageAdjustments?: boolean;
}

export type ProviderProductFormFieldErrors = Record<string, string>;

export type ProviderProductFormInput = Omit<ProductFormInput, "commerceId"> & {
  commerceId?: number;
};

/** SCRUM-361, Tarea 3.3: detalle de un pack afectado por bajar el stock de un componente. */
export interface AffectedPackage {
  packageId: number;
  packageTitle: string;
  commerceBranchId: number;
  currentQuantity: number;
  adjustedQuantity: number;
}
"use client";

import { useEffect, useState } from "react";
import { getProductCategories } from "@/lib/api";
import type { ProductCategoryFromAPI } from "@/types/products";

type UseProductCategoriesResult = {
  categories: ProductCategoryFromAPI[];
  categoriesLoading: boolean;
  categoriesError: string | null;
};

export function useProductCategories(): UseProductCategoriesResult {
  const [categories, setCategories] = useState<ProductCategoryFromAPI[]>([]);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [categoriesError, setCategoriesError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;

    const fetchCategories = async () => {
      try {
        setCategoriesLoading(true);
        setCategoriesError(null);

        const response = await getProductCategories({ page: 1, perPage: 100, status: "1" });

        if (!isMounted) {
          return;
        }

        setCategories(response.data ?? []);
      } catch {
        if (!isMounted) {
          return;
        }

        setCategoriesError("No pudimos cargar las categorías de producto.");
        setCategories([]);
      } finally {
        if (isMounted) {
          setCategoriesLoading(false);
        }
      }
    };

    void fetchCategories();

    return () => {
      isMounted = false;
    };
  }, []);

  return {
    categories,
    categoriesLoading,
    categoriesError,
  };
}

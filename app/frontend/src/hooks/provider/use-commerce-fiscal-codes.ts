"use client";

import { useEffect, useState } from "react";
import { getCommerceFiscalCodes } from "@/lib/api";
import type { FiscalCodeOption } from "@/types/products";

type UseCommerceFiscalCodesResult = {
  fiscalCodes: FiscalCodeOption[];
  fiscalCodesLoading: boolean;
  fiscalCodesError: string | null;
};

/**
 * SCRUM-362 (Tarea 5.2): códigos fiscales que el comercio autenticado puede
 * usar en sus productos, ya filtrados por tipo de establecimiento y
 * franquicia (FiscalCodeResolver, Tarea 2). Sin commerceId no hay nada que
 * pedir — el desplegable queda vacío hasta que el formulario lo resuelva.
 */
export function useCommerceFiscalCodes(commerceId?: number): UseCommerceFiscalCodesResult {
  const [fiscalCodes, setFiscalCodes] = useState<FiscalCodeOption[]>([]);
  const [fiscalCodesLoading, setFiscalCodesLoading] = useState(Boolean(commerceId));
  const [fiscalCodesError, setFiscalCodesError] = useState<string | null>(null);

  useEffect(() => {
    if (!commerceId) {
      setFiscalCodes([]);
      setFiscalCodesLoading(false);
      return;
    }

    let isMounted = true;

    const fetchFiscalCodes = async () => {
      try {
        setFiscalCodesLoading(true);
        setFiscalCodesError(null);

        const response = await getCommerceFiscalCodes(commerceId);

        if (!isMounted) {
          return;
        }

        setFiscalCodes(response.data ?? []);
      } catch {
        if (!isMounted) {
          return;
        }

        setFiscalCodesError("No pudimos cargar los códigos fiscales disponibles.");
        setFiscalCodes([]);
      } finally {
        if (isMounted) {
          setFiscalCodesLoading(false);
        }
      }
    };

    void fetchFiscalCodes();

    return () => {
      isMounted = false;
    };
  }, [commerceId]);

  return {
    fiscalCodes,
    fiscalCodesLoading,
    fiscalCodesError,
  };
}

"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { ApiError, getMyCommerce } from "@/lib/api";
import {
  normalizeCommerceVerificationStatus,
  type CommerceFromAPI,
  type CommerceVerificationStatus,
} from "@/types/commerces";

export type RegistrationStatus = "Aprobado" | "Pendiente" | "Rechazado" | "Por aprobar nuevamente";

type ProviderCommerceContextValue = {
  commerce: CommerceFromAPI | null;
  commerceId: number | null;
  registrationStatus: RegistrationStatus;
  isLoadingCommerce: boolean;
  /**
   * SCRUM-286: si la última carga falló por algo distinto de "no tiene
   * comercio" (404), `registrationStatus` cae a "Pendiente" como valor por
   * defecto — pero eso no es lo que le pasó al proveedor, es que no
   * pudimos averiguarlo. Quien muestre el estado al usuario debe preferir
   * este campo sobre `registrationStatus` cuando esté presente, para no
   * afirmar un estado que no se verificó.
   */
  commerceLoadError: string | null;
  refreshCommerce: () => Promise<void>;
};

const ProviderCommerceContext = createContext<ProviderCommerceContextValue | undefined>(undefined);

function resolveRegistrationStatus(isVerifiedValue: CommerceVerificationStatus): RegistrationStatus {
  if (isVerifiedValue === 1) {
    return "Aprobado";
  }

  if (isVerifiedValue === 2) {
    return "Rechazado";
  }

  if (isVerifiedValue === 3) {
    return "Por aprobar nuevamente";
  }

  return "Pendiente";
}

type ProviderCommerceProviderProps = {
  children: ReactNode;
};

export function ProviderCommerceProvider({ children }: ProviderCommerceProviderProps) {
  const [commerce, setCommerce] = useState<CommerceFromAPI | null>(null);
  const [commerceId, setCommerceId] = useState<number | null>(null);
  const [registrationStatus, setRegistrationStatus] = useState<RegistrationStatus>("Pendiente");
  const [isLoadingCommerce, setIsLoadingCommerce] = useState(true);
  const [commerceLoadError, setCommerceLoadError] = useState<string | null>(null);

  const refreshCommerce = useCallback(async () => {
    try {
      setIsLoadingCommerce(true);
      setCommerceLoadError(null);

      const response = await getMyCommerce();
      const commerce = response.data;

      if (!commerce) {
        setCommerce(null);
        setCommerceId(null);
        setRegistrationStatus("Pendiente");
        return;
      }

      setCommerce(commerce);
      setCommerceId(commerce.id);
      setRegistrationStatus(resolveRegistrationStatus(normalizeCommerceVerificationStatus(commerce.is_verified)));
    } catch (error) {
      if (error instanceof ApiError && error.status === 404) {
        setCommerce(null);
        setCommerceId(null);
        setRegistrationStatus("Pendiente");
        return;
      }

      console.error("Error cargando commerce del provider:", error);
      setRegistrationStatus("Pendiente");
      setCommerceLoadError("No pudimos consultar el estado de tu registro. Intenta de nuevo en unos minutos.");
    } finally {
      setIsLoadingCommerce(false);
    }
  }, []);

  useEffect(() => {
    void refreshCommerce();
  }, [refreshCommerce]);

  const value = useMemo<ProviderCommerceContextValue>(
    () => ({
      commerce,
      commerceId,
      registrationStatus,
      isLoadingCommerce,
      commerceLoadError,
      refreshCommerce,
    }),
    [commerce, commerceId, registrationStatus, isLoadingCommerce, commerceLoadError, refreshCommerce]
  );

  return <ProviderCommerceContext.Provider value={value}>{children}</ProviderCommerceContext.Provider>;
}

export function useProviderCommerce() {
  const context = useContext(ProviderCommerceContext);

  if (!context) {
    throw new Error("useProviderCommerce must be used within ProviderCommerceProvider");
  }

  return context;
}

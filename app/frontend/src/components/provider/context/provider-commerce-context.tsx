"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { ApiError, getMyCommerce } from "@/lib/api";
import type { CommerceFromAPI, CommerceVerificationStatus } from "@/types/commerces";

export type RegistrationStatus = "Aprobado" | "Pendiente" | "Rechazado";

type ProviderCommerceContextValue = {
  commerce: CommerceFromAPI | null;
  commerceId: number | null;
  registrationStatus: RegistrationStatus;
  isLoadingCommerce: boolean;
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

  const refreshCommerce = useCallback(async () => {
    try {
      setIsLoadingCommerce(true);

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
      setRegistrationStatus(resolveRegistrationStatus(commerce.is_verified));
    } catch (error) {
      if (error instanceof ApiError && error.status === 404) {
        setCommerce(null);
        setCommerceId(null);
        setRegistrationStatus("Pendiente");
        return;
      }

      console.error("Error cargando commerce del provider:", error);
      setRegistrationStatus("Pendiente");
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
      refreshCommerce,
    }),
    [commerce, commerceId, registrationStatus, isLoadingCommerce, refreshCommerce]
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

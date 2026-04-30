"use client";

import Link from "next/link";
import type { ReactNode } from "react";
import { useProviderCommerce } from "@/components/provider/context/provider-commerce-context";

type ProviderApprovedGateProps = {
  children: ReactNode;
  featureName: string;
};

export function ProviderApprovedGate({ children, featureName }: ProviderApprovedGateProps) {
  const { registrationStatus, isLoadingCommerce } = useProviderCommerce();

  if (isLoadingCommerce) {
    return (
      <div className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 text-[#6A6A6A] shadow-sm">
        Validando estado del registro...
      </div>
    );
  }

  if (registrationStatus === "Aprobado") {
    return <>{children}</>;
  }

  const blockedMessage =
    registrationStatus === "Rechazado"
      ? "Tu registro fue rechazado. Actualiza la informacion requerida y vuelve a enviarla para revision."
      : registrationStatus === "Por aprobar nuevamente"
        ? "Tus cambios fueron enviados y tu registro esta en nueva revision. Cuando sea validado nuevamente podras gestionar sucursales y productos."
      : "Tu registro aun no ha sido aprobado. Cuando sea validado podras gestionar sucursales y productos.";

  return (
    <div className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 md:p-8 shadow-sm">
      <h2 className="text-xl font-semibold text-[#1A1A1A]">Acceso restringido</h2>
      <p className="mt-2 text-sm text-[#6A6A6A]">
        Para usar {featureName}, el estado del registro debe estar en <strong>Aprobado</strong>.
      </p>
      <p className="mt-2 text-sm text-[#6A6A6A]">{blockedMessage}</p>

      <div className="mt-6 flex flex-wrap gap-3">
        <Link
          href="/provider/basic-info"
          className="px-5 h-[48px] inline-flex items-center rounded-[14px] bg-[#4B236A] text-white hover:bg-[#5D2B7D] transition-colors"
        >
          Revisar datos basicos
        </Link>
      </div>
    </div>
  );
}

"use client";

/**
 * SCRUM-286: la sección "Revisión" mostraba siempre el mismo mensaje fijo
 * ("solicitud enviada"), sin importar si el registro ya había sido
 * aprobado, rechazado, o reenviado a una nueva revisión. El estado real
 * (`is_verified`, 4 valores) ya llega por `useProviderCommerce()` — el
 * mismo contexto que ya usa `ProviderApprovedGate` para bloquear
 * Sucursales/Productos — así que esta tarjeta solo lo traduce a mensaje.
 */

import Link from "next/link";
import { AlertTriangle, CheckCircle2, Clock, XCircle } from "lucide-react";
import { useProviderCommerce } from "@/components/provider/context/provider-commerce-context";

type StatusPresentation = {
  icon: typeof CheckCircle2;
  iconClassName: string;
  borderClassName: string;
  backgroundClassName: string;
  title: string;
  message: string;
};

function presentationFor(
  registrationStatus: ReturnType<typeof useProviderCommerce>["registrationStatus"],
  isOnboardingSubmitted: boolean
): StatusPresentation {
  if (registrationStatus === "Aprobado") {
    return {
      icon: CheckCircle2,
      iconClassName: "text-[#4B236A]",
      borderClassName: "border-[#C8D86D]",
      backgroundClassName: "bg-[#DDE8BB]/40",
      title: "Registro aprobado",
      message: "Tu registro fue aprobado. Ya puedes crear tu sucursal y publicar productos.",
    };
  }

  if (registrationStatus === "Rechazado") {
    return {
      icon: XCircle,
      iconClassName: "text-red-600",
      borderClassName: "border-red-200",
      backgroundClassName: "bg-red-50",
      title: "Registro rechazado",
      message:
        "Tu solicitud fue rechazada. Revisa los comentarios del equipo Ñapa más abajo, actualiza la información y vuelve a enviarla.",
    };
  }

  if (registrationStatus === "Por aprobar nuevamente") {
    return {
      icon: AlertTriangle,
      iconClassName: "text-amber-600",
      borderClassName: "border-amber-200",
      backgroundClassName: "bg-amber-50",
      title: "En nueva revisión",
      message:
        "Enviaste cambios a tu registro y están en una nueva revisión. Te avisaremos cuando quede validado nuevamente.",
    };
  }

  return {
    icon: Clock,
    iconClassName: "text-[#4B236A]",
    borderClassName: isOnboardingSubmitted ? "border-[#C8D86D]" : "border-[#E0E0E0]",
    backgroundClassName: isOnboardingSubmitted ? "bg-[#DDE8BB]/40" : "bg-white",
    title: "Solicitud en revisión",
    message: isOnboardingSubmitted
      ? "Tu solicitud fue enviada. El proceso de validación toma 24 horas. Te notificaremos por correo si se requiere información adicional."
      : "Tu solicitud está en revisión. Te notificaremos por correo cuando sea validada.",
  };
}

type ProviderReviewStatusCardProps = {
  isOnboardingSubmitted: boolean;
};

export function ProviderReviewStatusCard({ isOnboardingSubmitted }: ProviderReviewStatusCardProps) {
  const { registrationStatus, isLoadingCommerce, commerceLoadError } = useProviderCommerce();

  if (isLoadingCommerce) {
    return (
      <div
        role="status"
        className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 md:p-8 text-sm text-[#6A6A6A]"
      >
        Consultando el estado de tu registro…
      </div>
    );
  }

  if (commerceLoadError) {
    return (
      <div
        role="alert"
        className="rounded-[18px] border border-red-200 bg-red-50 p-6 md:p-8"
      >
        <div className="flex items-start gap-3">
          <AlertTriangle className="mt-0.5 h-5 w-5 text-red-600" />
          <div>
            <h2 className="text-lg font-semibold text-[#1A1A1A]">No pudimos consultar tu estado</h2>
            <p className="mt-1 text-sm text-[#4A4A4A]">{commerceLoadError}</p>
          </div>
        </div>
      </div>
    );
  }

  const presentation = presentationFor(registrationStatus, isOnboardingSubmitted);
  const Icon = presentation.icon;

  return (
    <div
      role="status"
      className={`rounded-[18px] border p-6 md:p-8 ${presentation.borderClassName} ${presentation.backgroundClassName}`}
    >
      <div className="flex items-start gap-3">
        <div className="mt-0.5 rounded-full bg-[#4B236A]/10 p-2">
          <Icon className={`h-5 w-5 ${presentation.iconClassName}`} />
        </div>
        <div>
          <h2 className="text-lg font-semibold text-[#1A1A1A]">{presentation.title}</h2>
          <p className="mt-1 text-sm text-[#4A4A4A]">{presentation.message}</p>
        </div>
      </div>

      {registrationStatus === "Aprobado" && (
        <div className="mt-6 flex flex-wrap gap-3">
          <Link
            href="/provider/branches"
            className="px-5 h-[48px] inline-flex items-center rounded-[14px] bg-[#4B236A] text-white hover:bg-[#5D2B7D] transition-colors"
          >
            Crear sucursal
          </Link>
          <Link
            href="/provider/products"
            className="px-5 h-[48px] inline-flex items-center rounded-[14px] border border-[#4B236A] text-[#4B236A] hover:bg-[#4B236A]/5 transition-colors"
          >
            Crear producto
          </Link>
        </div>
      )}

      {registrationStatus === "Rechazado" && (
        <div className="mt-6">
          <Link
            href="/provider/basic-info"
            className="px-5 h-[48px] inline-flex items-center rounded-[14px] bg-[#4B236A] text-white hover:bg-[#5D2B7D] transition-colors"
          >
            Revisar datos básicos
          </Link>
        </div>
      )}
    </div>
  );
}

"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { AlertCircle, CheckCircle2, FileText } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/provider/ui/button";
import { acceptCommerceTerms, ApiError } from "@/lib/api";
import { useProviderCommerce } from "@/components/provider/context/provider-commerce-context";
import {
  TERMS_ACCEPTANCE_HELP_TEXT,
  TERMS_ACCEPTANCE_LABEL,
  TERMS_AND_CONDITIONS_SECTIONS,
} from "@/components/provider/legal/terms-and-conditions-content";

export function TermsAndConditionsPageClient() {
  const router = useRouter();
  const { commerce, commerceId, refreshCommerce, isLoadingCommerce } = useProviderCommerce();
  const [acceptedTerms, setAcceptedTerms] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const acceptanceHintId = "accept-terms-hint";
  const acceptanceWarningId = "accept-terms-warning";

  // Guard: Si términos ya fueron aceptados, mostrar estado de confirmación
  const termsAlreadyAccepted = commerce?.terms_accepted_at;

  // Guard: Si no hay commerceId y no está cargando, mostrar estado vacío accionable
  const showEmptyState = !commerceId && !isLoadingCommerce;

  const handleAcceptTerms = async () => {
    if (!acceptedTerms || !commerceId || isSubmitting) {
      return;
    }

    setIsSubmitting(true);

    try {
      await acceptCommerceTerms(commerceId, {
        terms_accepted_version: 1,
      });

      await refreshCommerce();

      toast.success("Términos aceptados", {
        description: "Tu solicitud fue enviada correctamente.",
      });

      // Navegar a revisión después de un breve delay para que el usuario vea el toast
      setTimeout(() => {
        router.push("/provider/review?onboarding=submitted");
      }, 500);
    } catch (error) {
      const errorMessage =
        error instanceof ApiError
          ? error.message
          : "No se pudo aceptar los términos. Intenta de nuevo.";

      toast.error("Error", {
        description: errorMessage,
      });

      console.error("Error aceptando términos:", error);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="mx-auto max-w-5xl">
      <header className="mb-8">
        <h1 className="text-2xl md:text-3xl font-bold text-[#1A1A1A]">
          Términos y condiciones
        </h1>
        <p className="mt-2 text-[#6A6A6A]">
          Lee el documento legal y confirma tu aceptación para continuar con el proceso.
        </p>
      </header>

      {/* Guard: Estado vacío si no hay commerce */}
      {showEmptyState && (
        <section className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 shadow-sm md:p-8">
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-[14px] bg-[#DDE8BB]">
              <FileText className="h-8 w-8 text-[#4B236A]" />
            </div>
            <h2 className="text-xl font-semibold text-[#1A1A1A]">
              Completa tus datos básicos primero
            </h2>
            <p className="mt-2 max-w-md text-sm text-[#6A6A6A]">
              Necesitamos que completes el formulario de datos básicos de tu comercio antes de poder aceptar los términos y condiciones.
            </p>
            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
              <Button
                type="button"
                variant="outline"
                onClick={() => router.back()}
                className="h-[52px] rounded-xl border-[#E0E0E0] text-[#4B236A] hover:bg-[#F7F7F7]"
              >
                Atrás
              </Button>
              <Button
                type="button"
                onClick={() => router.push("/provider/basic-info")}
                className="h-[52px] rounded-xl bg-[#4B236A] px-6 text-white shadow-lg transition-colors hover:bg-[#5D2B7D]"
              >
                Ir a datos básicos
              </Button>
            </div>
          </div>
        </section>
      )}

      {/* Guard: Badge de términos ya aceptados */}
      {termsAlreadyAccepted && !showEmptyState && (
        <section className="mb-6 rounded-[18px] border border-green-200 bg-green-50 p-6 shadow-sm md:p-8">
          <div className="flex items-start gap-4">
            <div
              aria-hidden
              className="flex h-12 w-12 items-center justify-center rounded-[14px] bg-green-200"
            >
              <CheckCircle2 className="h-6 w-6 text-green-700" />
            </div>
            <div className="flex-1">
              <h2 className="text-lg font-semibold text-green-900">
                Términos y condiciones aceptados
              </h2>
              <p className="mt-1 text-sm text-green-800">
                Ya has aceptado los términos y condiciones de Sumass. Revisa el estado de tu proceso de validación.
              </p>
            </div>
          </div>
          <div className="mt-6 flex justify-end gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={() => router.back()}
              className="h-[52px] rounded-xl border-[#DDE8BB] text-[#4B236A] hover:bg-[#DDE8BB]"
            >
              Atrás
            </Button>
            <Button
              type="button"
              onClick={() => router.push("/provider/review")}
              className="h-[52px] rounded-xl bg-[#4B236A] px-6 text-white shadow-lg transition-colors hover:bg-[#5D2B7D]"
            >
              Ver estado de revisión
            </Button>
          </div>
        </section>
      )}

      {/* Contenido principal: Formulario de aceptación */}
      {!showEmptyState && !termsAlreadyAccepted && (
        <section className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 shadow-sm md:p-8">
        <div className="mb-6 flex items-start gap-4">
          <div
            aria-hidden
            className="flex h-12 w-12 items-center justify-center rounded-[14px] bg-[#4B236A]/10"
          >
            <FileText className="h-6 w-6 text-[#4B236A]" />
          </div>

          <div>
            <h2 className="text-xl font-semibold text-[#1A1A1A]">
              Términos y condiciones de uso
            </h2>
            <p className="mt-1 text-sm text-[#6A6A6A]">Sumass - Panel de proveedores</p>
          </div>
        </div>

        <div className="mb-6 max-h-[360px] overflow-y-auto rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-5 md:max-h-[420px] md:p-6">
          <div className="space-y-5 text-sm leading-6 text-[#1A1A1A]">
            {TERMS_AND_CONDITIONS_SECTIONS.map((section) => (
              <section key={section.id}>
                <h3 className="font-semibold">{section.title}</h3>

                {section.paragraphs.map((paragraph) => (
                  <p key={paragraph} className="mt-2 text-[#6A6A6A]">
                    {paragraph}
                  </p>
                ))}

                {section.bullets?.length ? (
                  <ul className="mt-2 list-disc space-y-1 pl-5 text-[#6A6A6A]">
                    {section.bullets.map((bullet) => (
                      <li key={bullet}>{bullet}</li>
                    ))}
                  </ul>
                ) : null}
              </section>
            ))}
          </div>
        </div>

        <div className="rounded-[14px] border border-[#4B236A]/20 bg-[#4B236A]/5 p-5 md:p-6">
          <div className="flex items-start gap-3">
            <input
              id="accept-terms"
              name="accept-terms"
              type="checkbox"
              checked={acceptedTerms}
              onChange={(event) => setAcceptedTerms(event.target.checked)}
              aria-describedby={
                acceptedTerms
                  ? acceptanceHintId
                  : `${acceptanceHintId} ${acceptanceWarningId}`
              }
              className="mt-1 h-4 w-4 rounded border-[#E0E0E0] text-[#4B236A] focus-visible:ring-2 focus-visible:ring-[#4B236A] focus-visible:ring-offset-2"
            />

            <div className="flex-1">
              <label
                htmlFor="accept-terms"
                className="cursor-pointer text-sm font-medium leading-5 text-[#1A1A1A]"
              >
                {TERMS_ACCEPTANCE_LABEL}
              </label>
              <p id={acceptanceHintId} className="mt-2 text-sm text-[#6A6A6A]">
                {TERMS_ACCEPTANCE_HELP_TEXT}
              </p>
            </div>

            {acceptedTerms ? (
              <CheckCircle2 className="h-6 w-6 flex-shrink-0 text-[#4B236A]" />
            ) : null}
          </div>
        </div>

        {!acceptedTerms ? (
          <div
            id={acceptanceWarningId}
            role="alert"
            className="mt-4 flex items-start gap-2 rounded-[14px] border border-amber-200 bg-amber-50 p-4"
          >
            <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" />
            <p className="text-sm text-amber-800">
              Debes aceptar los términos y condiciones para continuar con el proceso de registro.
            </p>
          </div>
        ) : null}

        {!commerceId && !isLoadingCommerce ? (
          <div
            role="alert"
            className="mt-4 flex items-start gap-2 rounded-[14px] border border-amber-200 bg-amber-50 p-4"
          >
            <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" />
            <div className="flex-1">
              <p className="text-sm font-medium text-amber-900">
                Completa tus datos básicos primero
              </p>
              <p className="mt-1 text-sm text-amber-800">
                Debes completar el formulario de datos básicos antes de poder aceptar los términos.
              </p>
            </div>
          </div>
        ) : null}

        <div className="mt-6 flex justify-end gap-3">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.back()}
            className="h-[52px] rounded-xl border-[#DDE8BB] text-[#4B236A] hover:bg-[#DDE8BB] disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isSubmitting || isLoadingCommerce}
          >
            Atrás
          </Button>
          <Button
            type="button"
            disabled={!acceptedTerms || !commerceId || isSubmitting || isLoadingCommerce}
            className="h-[52px] w-full rounded-xl bg-[#4B236A] px-6 text-white shadow-lg transition-colors hover:bg-[#5D2B7D] focus-visible:ring-2 focus-visible:ring-[#4B236A] focus-visible:ring-offset-2 sm:w-auto disabled:cursor-not-allowed disabled:opacity-50"
            onClick={handleAcceptTerms}
          >
            {isSubmitting ? "Aceptando..." : "Aceptar y continuar"}
          </Button>
        </div>
      </section>
      )}
    </div>
  );
}

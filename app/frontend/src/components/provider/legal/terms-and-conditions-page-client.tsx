"use client";

import { useState } from "react";
import { AlertCircle, CheckCircle2, FileText } from "lucide-react";
import { Button } from "@/components/provider/ui/button";
import {
  TERMS_ACCEPTANCE_HELP_TEXT,
  TERMS_ACCEPTANCE_LABEL,
  TERMS_AND_CONDITIONS_SECTIONS,
} from "@/components/provider/legal/terms-and-conditions-content";

export function TermsAndConditionsPageClient() {
  const [acceptedTerms, setAcceptedTerms] = useState(false);
  const acceptanceHintId = "accept-terms-hint";
  const acceptanceWarningId = "accept-terms-warning";

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

        <div className="mt-6 flex justify-end">
          <Button
            type="button"
            disabled={!acceptedTerms}
            className="h-[52px] w-full rounded-xl bg-[#4B236A] px-6 text-white shadow-lg transition-colors hover:bg-[#5D2B7D] focus-visible:ring-2 focus-visible:ring-[#4B236A] focus-visible:ring-offset-2 sm:w-auto disabled:cursor-not-allowed disabled:opacity-50"
          >
            Aceptar y continuar
          </Button>
        </div>
      </section>
    </div>
  );
}

import type { Metadata } from "next";
import { CheckCircle2 } from "lucide-react";
import { ProviderMessageThread } from "@/components/provider/messages/provider-message-thread";

export const metadata: Metadata = {
  title: "Revisión | Panel Provider - Sumass",
  description: "Estado de revisión del proveedor",
};

type ReviewPageProps = {
  searchParams?: Promise<{
    onboarding?: string;
  }>;
};

const ONBOARDING_MESSAGE =
  "Tu solicitud fue enviada. El proceso de validación toma 24 horas. Te notificaremos por correo si se requiere información adicional.";

export default async function ReviewPage({ searchParams }: ReviewPageProps) {
  const resolvedSearchParams = searchParams ? await searchParams : undefined;
  const isOnboardingSubmitted = resolvedSearchParams?.onboarding === "submitted";

  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Revisión
        </h1>
        <p className="text-gray-600 mt-2">
          Estado de aprobación y observaciones
        </p>
      </div>

      <div className="space-y-4">
        <div
          className={`rounded-[18px] border p-6 md:p-8 ${
            isOnboardingSubmitted
              ? "border-[#C8D86D] bg-[#DDE8BB]/40"
              : "border-[#E0E0E0] bg-white"
          }`}
        >
          <div className="flex items-start gap-3">
            <div className="mt-0.5 rounded-full bg-[#4B236A]/10 p-2">
              <CheckCircle2 className="h-5 w-5 text-[#4B236A]" />
            </div>
            <div>
              <h2 className="text-lg font-semibold text-[#1A1A1A]">Solicitud enviada</h2>
              <p className="mt-1 text-sm text-[#4A4A4A]">{ONBOARDING_MESSAGE}</p>
            </div>
          </div>
        </div>

        <ProviderMessageThread />
      </div>
    </div>
  );
}

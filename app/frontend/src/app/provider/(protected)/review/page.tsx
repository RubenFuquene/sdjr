import type { Metadata } from "next";
import { ProviderMessageThread } from "@/components/provider/messages/provider-message-thread";
import { ProviderReviewStatusCard } from "@/components/provider/review/provider-review-status-card";

export const metadata: Metadata = {
  title: "Revisión | Panel Provider - Sumass",
  description: "Estado de revisión del proveedor",
};

type ReviewPageProps = {
  searchParams?: Promise<{
    onboarding?: string;
  }>;
};

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
        <ProviderReviewStatusCard isOnboardingSubmitted={isOnboardingSubmitted} />

        <ProviderMessageThread />
      </div>
    </div>
  );
}

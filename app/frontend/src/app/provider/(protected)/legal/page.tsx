import type { Metadata } from "next";
import { TermsAndConditionsPageClient } from "@/components/provider/legal/terms-and-conditions-page-client";

export const metadata: Metadata = {
  title: "Términos y condiciones | Panel Provider - Sumass",
  description: "Términos y condiciones del proveedor",
};

export default function LegalPage() {
  return (
    <div className="p-6 md:p-8">
      <TermsAndConditionsPageClient />
    </div>
  );
}

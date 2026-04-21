import type { Metadata } from "next";
import { ProductsPageClient } from "@/components/provider/products";
import { ProviderApprovedGate } from "@/components/provider/auth/provider-approved-gate";

export const metadata: Metadata = {
  title: "Productos | Panel Provider - Sumass",
  description: "Gestión de productos del proveedor",
};

export default function ProductsPage() {
  return (
    <div className="p-6 md:p-8">
      <ProviderApprovedGate featureName="Productos">
        <ProductsPageClient />
      </ProviderApprovedGate>
    </div>
  );
}

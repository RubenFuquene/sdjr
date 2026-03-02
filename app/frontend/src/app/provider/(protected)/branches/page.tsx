import type { Metadata } from "next";
import { BranchesPageClient } from "@/components/provider/branches/branches-page-client";

export const metadata: Metadata = {
  title: "Sucursales | Panel Provider - Sumass",
  description: "Gestión de sucursales del proveedor",
};

export default function BranchesPage() {
  return (
    <div className="p-6 md:p-8">
      <BranchesPageClient />
    </div>
  );
}

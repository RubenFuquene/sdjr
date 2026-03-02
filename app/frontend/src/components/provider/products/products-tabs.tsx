import type { ProductType } from "@/lib/api";

interface ProductsTabsProps {
  activeTab: ProductType;
  singleCount: number;
  packageCount: number;
  onTabChange: (tab: ProductType) => void;
}

export function ProductsTabs({
  activeTab,
  singleCount,
  packageCount,
  onTabChange,
}: ProductsTabsProps) {
  return (
    <div
      role="tablist"
      aria-label="Tipo de productos"
      className="inline-flex rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-1"
    >
      <button
        type="button"
        role="tab"
        aria-selected={activeTab === "single"}
        aria-controls="products-panel-single"
        id="products-tab-single"
        onClick={() => onTabChange("single")}
        className={`h-[42px] rounded-[12px] px-4 text-sm transition-colors ${
          activeTab === "single"
            ? "bg-white text-[#4B236A] shadow-sm"
            : "text-[#6A6A6A] hover:text-[#1A1A1A]"
        }`}
      >
        Individuales ({singleCount})
      </button>

      <button
        type="button"
        role="tab"
        aria-selected={activeTab === "package"}
        aria-controls="products-panel-package"
        id="products-tab-package"
        onClick={() => onTabChange("package")}
        className={`h-[42px] rounded-[12px] px-4 text-sm transition-colors ${
          activeTab === "package"
            ? "bg-white text-[#4B236A] shadow-sm"
            : "text-[#6A6A6A] hover:text-[#1A1A1A]"
        }`}
      >
        Packs ({packageCount})
      </button>
    </div>
  );
}

import { Plus } from "lucide-react";

interface AddProductButtonProps {
  label?: string;
  className?: string;
  ariaLabel?: string;
  onClick: () => void;
}

export function AddProductButton({
  label = "Agregar Producto",
  className = "",
  ariaLabel,
  onClick,
}: AddProductButtonProps) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={ariaLabel ?? label}
      className={`inline-flex items-center justify-center bg-[#4B236A] hover:bg-[#5D2B7D] text-white rounded-[14px] h-[52px] px-6 shadow-md transition-colors ${className}`}
    >
      <Plus className="w-4 h-4 mr-2" />
      {label}
    </button>
  );
}

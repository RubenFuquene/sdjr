import Link from "next/link";
import { Plus } from "lucide-react";

type AddBranchButtonProps = {
  label?: string;
  className?: string;
  ariaLabel?: string;
  onClick?: () => void;
};

export function AddBranchButton({
  label = "Agregar Sucursal",
  className = "",
  ariaLabel,
  onClick,
}: AddBranchButtonProps) {
  if (onClick) {
    return (
      <button
        type="button"
        onClick={onClick}
        aria-label={ariaLabel ?? label}
        className={`inline-flex items-center justify-center bg-[#4B236A] hover:bg-[#4B236A]/90 text-white rounded-[14px] h-[52px] px-6 shadow-md transition-colors ${className}`}
      >
        <Plus className="w-4 h-4 mr-2" />
        {label}
      </button>
    );
  }

  return (
    <Link
      href="/provider/branches/new"
      aria-label={ariaLabel ?? label}
      className={`inline-flex items-center justify-center bg-[#4B236A] hover:bg-[#4B236A]/90 text-white rounded-[14px] h-[52px] px-6 shadow-md transition-colors ${className}`}
    >
      <Plus className="w-4 h-4 mr-2" />
      {label}
    </Link>
  );
}

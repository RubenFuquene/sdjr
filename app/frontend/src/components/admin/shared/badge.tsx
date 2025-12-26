import { ReactNode } from "react";

type BadgeVariant = "success" | "inactive" | "perfil" | "permiso";

interface BadgeProps {
  children: ReactNode;
  variant?: BadgeVariant;
}

const variantStyles: Record<BadgeVariant, string> = {
  success: "bg-[#10B981]/20 text-[#10B981]",
  inactive: "bg-[#F7F7F7] text-[#6A6A6A]",
  perfil: "bg-[#4B236A]/10 text-[#4B236A]",
  permiso: "bg-[#DDE8BB] text-[#4B236A]",
};

export function Badge({ children, variant = "perfil" }: BadgeProps) {
  return (
    <span className={`px-3 py-1 rounded-full text-xs inline-block ${variantStyles[variant]}`}>
      {children}
    </span>
  );
}

export function StatusBadge({ activo }: { activo: boolean }) {
  return (
    <Badge variant={activo ? "success" : "inactive"}>
      {activo ? "Activo" : "Inactivo"}
    </Badge>
  );
}

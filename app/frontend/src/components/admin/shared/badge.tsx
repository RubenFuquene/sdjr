import { ReactNode } from "react";

type BadgeVariant = "success" | "inactive" | "perfil" | "permiso" | "warning";

interface BadgeProps {
  children: ReactNode;
  variant?: BadgeVariant;
}

const variantStyles: Record<BadgeVariant, string> = {
  success: "bg-[#10B981]/20 text-[#10B981]",
  inactive: "bg-[#F7F7F7] text-[#6A6A6A]",
  perfil: "bg-[#4B236A]/10 text-[#4B236A]",
  permiso: "bg-[#DDE8BB] text-[#4B236A]",
  warning: "bg-[#F97316]/20 text-[#F97316]",
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

export function VerificationStatusBadge({ estado }: { estado: 0 | 1 | 2 }) {
  const variantMap: Record<0 | 1 | 2, "inactive" | "success" | "warning"> = {
    0: "inactive",
    1: "success",
    2: "warning",
  };

  const labelMap: Record<0 | 1 | 2, string> = {
    0: "Pendiente",
    1: "Activo",
    2: "Rechazado",
  };

  return (
    <Badge variant={variantMap[estado] as any}>
      {labelMap[estado]}
    </Badge>
  );
}

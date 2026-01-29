import type { Role } from "@/types/auth";

/**
 * Map Laravel role names to frontend role types
 * 
 * @param laravelRole - Role name from Laravel API
 * @returns Frontend role type
 */
export function mapLaravelRoleToRole(laravelRole: string): Role {
  switch (laravelRole) {
    case "provider":
      return "provider";
    case "customer":
      return "app";
    case "admin":
    case "superadmin":
      return "admin";
    default:
      // Fallback seguro: tratar roles desconocidos como admin
      console.warn(`Unknown Laravel role: ${laravelRole}, defaulting to admin`);
      return "admin";
  }
}

/**
 * Get dashboard path for a given role
 */
export function getDashboardPath(role: Role): string {
  const paths: Record<Role, string> = {
    admin: "/admin/dashboard",
    provider: "/provider/dashboard",
    app: "/app/dashboard",
  };
  return paths[role];
}

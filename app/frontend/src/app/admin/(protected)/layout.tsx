import type { ReactNode } from "react";
import { getSessionOrRedirect } from "@/lib/auth";
import { DashboardShell } from "@/components/admin/dashboard-shell";

export default async function AdminProtectedLayout({ children }: { children: ReactNode }) {
  // Guard server-side (dummy mientras no hay backend real). Requiere cookies seteadas en el login stub.
  await getSessionOrRedirect("admin");
  return <DashboardShell activePath="/admin/dashboard">{children}</DashboardShell>;
}

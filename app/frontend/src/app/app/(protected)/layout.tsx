import type { ReactNode } from "react";
import { getSessionOrRedirect } from "@/lib/auth";
import { AppMobileShell } from "@/components/app/shell/app-mobile-shell";

export const dynamic = "force-dynamic";

type AppProtectedLayoutProps = {
  children: ReactNode;
};

export default async function AppProtectedLayout({ children }: AppProtectedLayoutProps) {
  await getSessionOrRedirect("user");

  return <AppMobileShell>{children}</AppMobileShell>;
}

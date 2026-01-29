import { getSessionOrRedirect } from "@/lib/auth";
import { ProviderShellWrapper } from "@/components/provider/shell/provider-shell-wrapper";
import type { ReactNode } from "react";

export const dynamic = "force-dynamic";

type ProviderProtectedLayoutProps = {
  children: ReactNode;
};

export default async function ProviderProtectedLayout({
  children,
}: ProviderProtectedLayoutProps) {
  const session = await getSessionOrRedirect("provider");

  return <ProviderShellWrapper userData={session}>{children}</ProviderShellWrapper>;
}

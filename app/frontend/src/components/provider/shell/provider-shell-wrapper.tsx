"use client";

import { usePathname } from "next/navigation";
import { ProviderShell } from "@/components/provider/shell/provider-shell";
import { ProviderCommerceProvider } from "@/components/provider/context/provider-commerce-context";
import type { SessionData } from "@/types/auth";
import type { ReactNode } from "react";

type ProviderShellWrapperProps = {
  children: ReactNode;
  userData: SessionData;
};

export function ProviderShellWrapper({ children, userData }: ProviderShellWrapperProps) {
  const pathname = usePathname();
  
  return (
    <ProviderCommerceProvider>
      <ProviderShell activePath={pathname} userData={userData}>
        {children}
      </ProviderShell>
    </ProviderCommerceProvider>
  );
}

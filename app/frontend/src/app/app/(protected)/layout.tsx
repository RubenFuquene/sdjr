import type { ReactNode } from "react";
import { getSessionOrRedirect } from "@/lib/auth";

export const dynamic = "force-dynamic";

type AppProtectedLayoutProps = {
  children: ReactNode;
};

export default async function AppProtectedLayout({ children }: AppProtectedLayoutProps) {
  await getSessionOrRedirect("user");

  return <div className="min-h-screen bg-[#F5F5F5]">{children}</div>;
}

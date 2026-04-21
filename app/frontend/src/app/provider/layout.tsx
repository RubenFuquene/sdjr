import type { ReactNode } from "react";

export const metadata = {
  title: "Provider | Sumass",
};

export default function ProviderLayout({ children }: { children: ReactNode }) {
  return <>{children}</>;
}

import type { ReactNode } from "react";

export const metadata = {
  title: "Admin | Sumass",
};

export default function AdminLayout({ children }: { children: ReactNode }) {
  return <div className="min-h-screen">{children}</div>;
}

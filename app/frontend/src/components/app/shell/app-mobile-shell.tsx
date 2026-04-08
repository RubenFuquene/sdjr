import type { ReactNode } from "react";
import { AppBottomNav } from "@/components/app/shell/app-bottom-nav";

type AppMobileShellProps = {
  children: ReactNode;
};

export function AppMobileShell({ children }: AppMobileShellProps) {
  return (
    <div className="app-shell min-h-screen bg-[var(--color-app-ui-background-soft)] sm:px-4 sm:py-3">
      <div
        className="mx-auto flex min-h-screen w-full max-w-[430px] flex-col bg-[var(--color-app-ui-background)] shadow-none sm:min-h-[calc(100vh-24px)] sm:rounded-[28px] sm:shadow-[0_16px_48px_rgba(0,0,0,0.18)]"
        style={{
          paddingTop: "env(safe-area-inset-top)",
        }}
      >
        <main className="flex-1 overflow-y-auto">
          {children}
        </main>

        <AppBottomNav />
      </div>
    </div>
  );
}

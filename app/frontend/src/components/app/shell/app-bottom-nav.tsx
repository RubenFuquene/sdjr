"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Heart, Home, ShoppingBag, User } from "lucide-react";
import { APP_TAB_ITEMS, isAppTabActive, type AppTabKey } from "@/lib/app/navigation";
import { cn } from "@/components/app/ui/utils";

const TAB_ICONS: Record<AppTabKey, typeof Home> = {
  discover: Home,
  orders: ShoppingBag,
  favorites: Heart,
  profile: User,
};

export function AppBottomNav() {
  const pathname = usePathname();

  return (
    <nav
      className="sticky bottom-0 z-20 border-t border-[var(--color-app-ui-divider)] bg-[var(--color-app-ui-background)] px-2 pb-[calc(10px+env(safe-area-inset-bottom))] pt-2 shadow-[0_-2px_8px_rgba(0,0,0,0.06)]"
      aria-label="Navegacion principal app"
    >
      <ul className="grid grid-cols-4 gap-1">
        {APP_TAB_ITEMS.map((item) => {
          const isActive = isAppTabActive(pathname, item);
          const Icon = TAB_ICONS[item.key];

          return (
            <li key={item.key}>
              <Link
                href={item.href}
                className={cn(
                  "flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-xs transition-colors",
                  isActive
                    ? "bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]"
                    : "text-[var(--color-app-text-secondary-purple)] hover:bg-[var(--color-app-ui-background-soft)]"
                )}
                aria-current={isActive ? "page" : undefined}
              >
                <Icon
                  className="h-5 w-5"
                  fill={item.key === "favorites" && isActive ? "currentColor" : "none"}
                />
                <span>{item.label}</span>
              </Link>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}

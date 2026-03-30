export type AppTabKey = "discover" | "orders" | "favorites" | "profile";

export type AppTabItem = {
  key: AppTabKey;
  label: string;
  href: string;
  activePrefixes: string[];
};

export const APP_TAB_ITEMS: AppTabItem[] = [
  {
    key: "discover",
    label: "Descubre",
    href: "/app/discover",
    activePrefixes: ["/app/discover", "/app/dashboard"],
  },
  {
    key: "orders",
    label: "Pedidos",
    href: "/app/orders",
    activePrefixes: ["/app/orders"],
  },
  {
    key: "favorites",
    label: "Favoritos",
    href: "/app/favorites",
    activePrefixes: ["/app/favorites"],
  },
  {
    key: "profile",
    label: "Perfil",
    href: "/app/profile",
    activePrefixes: ["/app/profile"],
  },
];

export function isAppTabActive(pathname: string, item: AppTabItem): boolean {
  return item.activePrefixes.some((prefix) => {
    return pathname === prefix || pathname.startsWith(`${prefix}/`);
  });
}

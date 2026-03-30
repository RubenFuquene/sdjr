"use client";

import { useRouter } from "next/navigation";
import { ChevronRight, Headphones, Leaf, LogOut, Tag, Wallet } from "lucide-react";
import { clearSession } from "@/lib/session";

const PROFILE_USER = {
  name: "Maria Garcia",
  email: "maria.garcia@email.com",
  initials: "MG",
  savedKg: 3.2,
  totalOrders: 8,
  savedMoney: 64000,
};

type ProfileActionItem = {
  id: string;
  label: string;
  description: string;
  icon: "wallet" | "coupon" | "support";
};

const ACTIONS: ProfileActionItem[] = [
  {
    id: "wallet",
    label: "Mi Billetera",
    description: "$45.000 disponibles",
    icon: "wallet",
  },
  {
    id: "coupons",
    label: "Mis Cupones",
    description: "Redimir cupones",
    icon: "coupon",
  },
  {
    id: "support",
    label: "Soporte",
    description: "Ayuda y contacto",
    icon: "support",
  },
];

function ActionIcon({ icon }: { icon: ProfileActionItem["icon"] }) {
  if (icon === "wallet") {
    return <Wallet className="h-5 w-5 text-[var(--color-app-text-primary-purple)]" />;
  }

  if (icon === "coupon") {
    return <Tag className="h-5 w-5 text-[var(--color-app-text-primary-purple)]" />;
  }

  return <Headphones className="h-5 w-5 text-[var(--color-app-text-primary-purple)]" />;
}

export default function AppProfilePage() {
  const router = useRouter();

  const handleActionClick = (actionId: string) => {
    if (actionId === "support") {
      router.push("/app/support");
    }
  };

  const handleLogout = () => {
    clearSession();
    router.push("/app/login");
  };

  return (
    <section className="pb-6">
      <header className="rounded-b-3xl bg-gradient-to-b from-[var(--color-app-text-primary-purple)] to-[var(--color-app-text-secondary-purple)] px-6 pb-20 pt-8">
        <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl text-[var(--color-app-text-primary-purple)]">
          {PROFILE_USER.initials}
        </div>
        <h1 className="mt-4 text-center text-2xl text-white">{PROFILE_USER.name}</h1>
        <p className="mt-1 text-center text-sm text-white/80">{PROFILE_USER.email}</p>
      </header>

      <div className="-mt-12 px-4">
        <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-5 shadow-[var(--app-shadow-card)]">
          <div className="mb-4 flex items-center gap-2">
            <Leaf className="h-5 w-5 text-[var(--color-app-tomatillo-medium)]" />
            <h2 className="text-base text-[var(--color-app-text-dark)]">Tu impacto ambiental</h2>
          </div>

          <div className="grid grid-cols-3 gap-3 text-center">
            <div>
              <p className="text-2xl text-[var(--color-app-tomatillo-medium)]">{PROFILE_USER.savedKg}</p>
              <p className="text-xs text-[var(--color-app-text-secondary-purple)]">kg salvados</p>
            </div>
            <div>
              <p className="text-2xl text-[var(--color-app-tomatillo-medium)]">{PROFILE_USER.totalOrders}</p>
              <p className="text-xs text-[var(--color-app-text-secondary-purple)]">rescates</p>
            </div>
            <div>
              <p className="text-2xl text-[var(--color-app-tomatillo-medium)]">
                ${(PROFILE_USER.savedMoney / 1000).toFixed(0)}k
              </p>
              <p className="text-xs text-[var(--color-app-text-secondary-purple)]">ahorrados</p>
            </div>
          </div>
        </div>
      </div>

      <div className="mt-4 space-y-3 px-4">
        {ACTIONS.map((action) => (
          <button
            key={action.id}
            type="button"
            onClick={() => handleActionClick(action.id)}
            className="flex w-full items-center justify-between rounded-2xl bg-[var(--color-app-ui-background)] p-4 text-left shadow-[var(--app-shadow-card)]"
          >
            <div className="flex items-center gap-3">
              <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--color-app-tomatillo-soft)]">
                <ActionIcon icon={action.icon} />
              </div>
              <div>
                <p className="text-sm text-[var(--color-app-text-dark)]">{action.label}</p>
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{action.description}</p>
              </div>
            </div>
            <ChevronRight className="h-5 w-5 text-[var(--color-app-text-secondary-purple)]" />
          </button>
        ))}

        <button
          type="button"
          onClick={handleLogout}
          className="flex w-full items-center justify-between rounded-2xl bg-[var(--color-app-ui-background)] p-4 text-left shadow-[var(--app-shadow-card)]"
        >
          <div className="flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#FDECEC]">
              <LogOut className="h-5 w-5 text-[var(--color-app-status-error)]" />
            </div>
            <span className="text-sm text-[var(--color-app-status-error)]">Cerrar sesion</span>
          </div>
          <ChevronRight className="h-5 w-5 text-[var(--color-app-text-secondary-purple)]" />
        </button>
      </div>
    </section>
  );
}

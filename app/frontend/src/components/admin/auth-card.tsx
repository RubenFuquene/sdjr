import Image from "next/image";
import { ReactNode } from "react";

type AuthCardProps = {
  title: string;
  subtitle?: string;
  children: ReactNode;
};

export function AuthCard({ title, subtitle, children }: AuthCardProps) {
  return (
    <div className="w-full max-w-md rounded-2xl bg-white px-6 py-8 shadow-login-card sm:px-8 sm:py-10">
      <div className="flex flex-col items-center gap-2 text-center text-[var(--color-text)]">
        <Logo />
        <h1 className="text-xl font-semibold">{title}</h1>
        {subtitle ? <p className="text-sm text-[var(--color-muted)]">{subtitle}</p> : null}
      </div>
      <div className="mt-8">{children}</div>
    </div>
  );
}

function Logo() {
  return (
    <Image
      src="/brand/logo-su.svg"
      alt="Sumass"
      width={82}
      height={82}
      className="h-[82px] w-[82px]"
      priority
    />
  );
}

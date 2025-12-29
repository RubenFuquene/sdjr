import Image from "next/image";
import { ReactNode } from "react";

type AuthCardProps = {
  title: string;
  subtitle?: string;
  children: ReactNode;
};

export function AuthCard({ title, subtitle, children }: AuthCardProps) {
  return (
    <div className="w-full max-w-md rounded-[18px] bg-white px-8 py-8 shadow-2xl">
      <div className="mb-8 flex flex-col items-center gap-3 text-center">
        {/* Logo container - Figma spec */}
        <div className="inline-block bg-white p-4 rounded-[18px] mb-4">
          <Logo />
        </div>
        <h1 className="text-xl font-semibold text-[var(--color-text)]">{title}</h1>
        {subtitle ? <p className="text-sm text-[var(--color-muted)]">{subtitle}</p> : null}
      </div>
      <div>{children}</div>
    </div>
  );
}

function Logo() {
  return (
    <Image
      src="/brand/logo-sumass.png"
      alt="Sumass"
      width={96}
      height={96}
      className="h-24 w-24 object-contain"
      priority
    />
  );
}

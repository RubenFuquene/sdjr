import { ReactNode } from "react";

interface PageHeaderProps {
  children: ReactNode;
}

export function PageHeader({ children }: PageHeaderProps) {
  return (
    <div className="flex items-center justify-between mb-6">
      {children}
    </div>
  );
}

PageHeader.Content = function PageHeaderContent({ children }: { children: ReactNode }) {
  return <div>{children}</div>;
};

PageHeader.Title = function PageHeaderTitle({ children }: { children: ReactNode }) {
  return <h1 className="text-2xl font-semibold text-[#1A1A1A]">{children}</h1>;
};

PageHeader.Description = function PageHeaderDescription({ children }: { children: ReactNode }) {
  return <p className="text-sm text-[#6A6A6A] mt-1">{children}</p>;
};

PageHeader.Actions = function PageHeaderActions({ children }: { children: ReactNode }) {
  return <div className="flex gap-3">{children}</div>;
};

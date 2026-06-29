import { Suspense } from "react";
import { AuthCard } from "@/components/admin/auth-card";
import { AdminResetPasswordForm } from "@/components/admin/reset-password-form";

export const metadata = {
  title: "Restablecer Contraseña | Sumass",
  description: "Restablece tu contraseña usando el token de recuperación recibido por correo",
};

interface AdminResetPasswordPageProps {
  searchParams?: Promise<{
    email?: string | string[];
    token?: string | string[];
  }>;
}

function getSingleQueryParam(param?: string | string[]): string {
  if (typeof param === "string") return param;
  if (Array.isArray(param) && param.length > 0) return param[0] || "";
  return "";
}

export default async function AdminResetPasswordPage({ searchParams }: AdminResetPasswordPageProps) {
  const resolvedSearchParams = searchParams ? await searchParams : undefined;
  const initialEmail = getSingleQueryParam(resolvedSearchParams?.email);
  const initialToken = getSingleQueryParam(resolvedSearchParams?.token);

  return (
    <div className="bg-login-gradient flex min-h-screen items-center justify-center px-4 py-10 sm:py-16 md:py-20">
      <AuthCard title="Restablecer contraseña" subtitle="Tu Sumass al planeta">
        <Suspense fallback={null}>
          <AdminResetPasswordForm initialEmail={initialEmail} initialToken={initialToken} />
        </Suspense>
      </AuthCard>
    </div>
  );
}

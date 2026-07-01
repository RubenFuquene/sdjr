import { Suspense } from "react";
import AppLoginVisual from "@/components/app/auth/app-login-visual";
import AppResetPasswordForm from "@/components/app/auth/app-reset-password-form";

export const metadata = {
  title: "Restablecer Contraseña | Sumass",
  description: "Restablece tu contraseña usando el token de recuperación recibido por correo",
};

interface AppResetPasswordPageProps {
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

export default async function AppResetPasswordPage({ searchParams }: AppResetPasswordPageProps) {
  const resolvedSearchParams = searchParams ? await searchParams : undefined;
  const initialEmail = getSingleQueryParam(resolvedSearchParams?.email);
  const initialToken = getSingleQueryParam(resolvedSearchParams?.token);

  return (
    <div className="min-h-screen w-full bg-[#F5F5F5] px-4 py-6 sm:py-10">
      <div className="mx-auto flex w-full max-w-md flex-col overflow-hidden bg-white sm:rounded-3xl sm:shadow-lg">
        <AppLoginVisual />
        <div className="px-6 py-6">
          <Suspense fallback={null}>
            <AppResetPasswordForm initialEmail={initialEmail} initialToken={initialToken} />
          </Suspense>
        </div>
      </div>
    </div>
  );
}

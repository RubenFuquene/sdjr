import { AuthBranding } from "@/components/provider/auth/auth-branding";
import { ResetPasswordForm } from "@/components/provider/auth/reset-password-form";

export const metadata = {
  title: "Restablecer Contraseña | Sumass",
  description: "Restablece tu contraseña usando el token de recuperación recibido por correo",
};

interface ResetPasswordPageProps {
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

export default async function ProviderResetPasswordPage({ searchParams }: ResetPasswordPageProps) {
  const resolvedSearchParams = searchParams ? await searchParams : undefined;
  const initialEmail = getSingleQueryParam(resolvedSearchParams?.email);
  const initialToken = getSingleQueryParam(resolvedSearchParams?.token);

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#DDE8BB]/30 via-white to-[#DDE8BB]/10 flex items-center justify-center p-4">
      <div className="w-full max-w-6xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <AuthBranding />

          <div className="flex items-center justify-center">
            <div className="w-full max-w-md bg-white rounded-[18px] shadow-lg border-0 p-8">
              <ResetPasswordForm initialEmail={initialEmail} initialToken={initialToken} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

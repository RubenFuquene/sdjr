import { AuthCard } from "@/components/admin/auth-card";
import { AdminForgotPasswordForm } from "@/components/admin/forgot-password-form";

export const metadata = {
  title: "Recuperar Contraseña | Sumass",
  description: "Solicita instrucciones para restablecer tu contraseña de administrador",
};

export default function AdminForgotPasswordPage() {
  return (
    <div className="bg-login-gradient flex min-h-screen items-center justify-center px-4 py-10 sm:py-16 md:py-20">
      <AuthCard title="Recuperar contraseña" subtitle="Tu Sumass al planeta">
        <AdminForgotPasswordForm />
      </AuthCard>
    </div>
  );
}

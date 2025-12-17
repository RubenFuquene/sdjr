import { AuthCard } from "@/components/admin/auth-card";
import { LoginForm } from "@/components/admin/login-form";

const copy = {
  es: {
    title: "Panel Administrativo",
    subtitle: "Tu Sumass al planeta",
    emailLabel: "Correo Corporativo",
    emailPlaceholder: "admin@sumass.com",
    passwordLabel: "Contraseña",
    passwordPlaceholder: "••••••••",
    forgot: "¿Olvidaste tu contraseña?",
    submit: "Ingresar",
    errorGeneric: "No pudimos iniciar sesión. Intenta nuevamente.",
  },
};

export const metadata = {
  title: "Login Admin | Sumass",
};

export default function AdminLoginPage() {
  const t = copy.es;
  return (
    <div className="bg-login-gradient flex min-h-screen items-center justify-center px-4 py-10 sm:py-16 md:py-20">
      <AuthCard title={t.title} subtitle={t.subtitle}>
        <LoginForm
          labels={{
            emailLabel: t.emailLabel,
            emailPlaceholder: t.emailPlaceholder,
            passwordLabel: t.passwordLabel,
            passwordPlaceholder: t.passwordPlaceholder,
            forgot: t.forgot,
            submit: t.submit,
            errorGeneric: t.errorGeneric,
          }}
        />
      </AuthCard>
    </div>
  );
}

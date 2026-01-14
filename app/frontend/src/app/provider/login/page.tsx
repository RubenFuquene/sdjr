import { ProviderAuthPage } from "@/components/provider/auth/provider-auth-page";

export const metadata = {
  title: "Proveedores | Sumass",
  description: "Panel de control para proveedores - Inicia sesión o crea tu cuenta",
};

export default function ProviderLoginPage() {
  return <ProviderAuthPage defaultTab="login" />;
}

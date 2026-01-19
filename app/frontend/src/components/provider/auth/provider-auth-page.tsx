import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/provider/ui/tabs";
import { LoginForm } from "./login-form";
import { RegisterForm } from "./register-form";
import { AuthBranding } from "./auth-branding";

interface ProviderAuthPageProps {
  defaultTab?: "login" | "register";
}

export function ProviderAuthPage({ defaultTab = "login" }: ProviderAuthPageProps) {
  return (
    <div className="min-h-screen bg-gradient-to-br from-[#DDE8BB]/30 via-white to-[#DDE8BB]/10 flex items-center justify-center p-4">
      <div className="w-full max-w-6xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          {/* Branding - Solo visible en desktop */}
          <AuthBranding />

          {/* Formularios */}
          <div className="flex items-center justify-center">
            <div className="w-full max-w-md bg-white rounded-[18px] shadow-lg border-0 p-8">
              <Tabs defaultValue={defaultTab} className="w-full">
                <TabsList className="grid w-full grid-cols-2 bg-[#DDE8BB]/20 rounded-[14px] p-1">
                  <TabsTrigger
                    value="login"
                    className="rounded-[12px] data-[state=active]:bg-[#DDE8BB] data-[state=active]:text-[#4B236A] text-gray-600 font-medium transition-all duration-200"
                  >
                    Iniciar Sesión
                  </TabsTrigger>
                  <TabsTrigger
                    value="register"
                    className="rounded-[12px] data-[state=active]:bg-[#DDE8BB] data-[state=active]:text-[#4B236A] text-gray-600 font-medium transition-all duration-200"
                  >
                    Registro
                  </TabsTrigger>
                </TabsList>

                {/* Tab Login */}
                <TabsContent value="login" className="mt-8">
                  <LoginForm />
                </TabsContent>

                {/* Tab Registro */}
                <TabsContent value="register" className="mt-8">
                  <RegisterForm />
                </TabsContent>
              </Tabs>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

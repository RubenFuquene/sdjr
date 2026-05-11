"use client";

import { useState } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/provider/ui/tabs";
import { LoginForm } from "./login-form";
import { RegisterForm } from "./register-form";
import { AuthBranding } from "./auth-branding";
import { ForgotPasswordForm } from "@/components/provider/auth/forgot-password-form";

interface ProviderAuthPageProps {
  defaultTab?: "login" | "register" | "forgot-password";
}

export function ProviderAuthPage({ defaultTab = "login" }: ProviderAuthPageProps) {
  const [activeTab, setActiveTab] = useState<"login" | "register" | "forgot-password">(defaultTab);

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#DDE8BB]/30 via-white to-[#DDE8BB]/10 flex items-center justify-center p-4">
      <div className="w-full max-w-6xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          {/* Branding - Solo visible en desktop */}
          <AuthBranding />

          {/* Formularios */}
          <div className="flex items-center justify-center">
            <div className="w-full max-w-md bg-white rounded-[18px] shadow-lg border-0 p-8">
              <Tabs value={activeTab} onValueChange={(value) => setActiveTab(value as "login" | "register" | "forgot-password")} className="w-full">
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
                  <LoginForm onForgotPassword={() => setActiveTab("forgot-password")} />
                </TabsContent>

                {/* Tab Registro */}
                <TabsContent value="register" className="mt-8">
                  <RegisterForm onSwitchToLogin={() => setActiveTab("login")} />
                </TabsContent>

                {/* Recover Password Panel */}
                <TabsContent value="forgot-password" className="mt-8">
                  <ForgotPasswordForm onBackToLogin={() => setActiveTab("login")} />
                </TabsContent>
              </Tabs>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

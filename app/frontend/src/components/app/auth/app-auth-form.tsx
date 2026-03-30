"use client";

import { useState } from "react";
import AppLoginForm from "@/components/app/auth/app-login-form";
import AppRegisterForm from "@/components/app/auth/app-register-form";

type ActiveTab = "login" | "register";

export default function AppAuthForm() {
  const [activeTab, setActiveTab] = useState<ActiveTab>("login");

  return (
    <div className="px-6 py-6">
      <div className="mb-6 flex gap-1 rounded-2xl bg-[#F5F5F5] p-1">
        <button
          type="button"
          onClick={() => setActiveTab("login")}
          aria-pressed={activeTab === "login"}
          className={`flex-1 rounded-xl py-3 text-sm font-medium transition-colors ${
            activeTab === "login"
              ? "bg-[#5A1E6B] text-white"
              : "text-[#7A2E9A] hover:bg-[#DDE8BB]"
          }`}
        >
          Iniciar sesión
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("register")}
          aria-pressed={activeTab === "register"}
          className={`flex-1 rounded-xl py-3 text-sm font-medium transition-colors ${
            activeTab === "register"
              ? "bg-[#5A1E6B] text-white"
              : "text-[#7A2E9A] hover:bg-[#DDE8BB]"
          }`}
        >
          Registrarse
        </button>
      </div>

      {activeTab === "login" ? <AppLoginForm /> : <AppRegisterForm />}
    </div>
  );
}

import { Suspense } from "react";
import AppLoginForm from "@/components/app/auth/app-login-form";
import AppLoginVisual from "@/components/app/auth/app-login-visual";

export default function AppLoginPage() {
  return (
    <div className="min-h-screen w-full bg-[#F5F5F5] px-4 py-6 sm:py-10">
      <div className="mx-auto flex w-full max-w-md flex-col overflow-hidden bg-white sm:rounded-3xl sm:shadow-lg">
        <AppLoginVisual />
        <Suspense fallback={null}>
          <AppLoginForm />
        </Suspense>
      </div>
    </div>
  );
}

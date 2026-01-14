import Image from "next/image";

export const metadata = {
  title: "Login Provider | Sumass",
};

export default function ProviderLoginPage() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-[#DDE8BB]/30 via-white to-[#DDE8BB]/10 px-4">
      <div className="w-full max-w-md rounded-[18px] bg-white p-8 shadow-2xl">
        <div className="mb-8 text-center">
          <div className="mb-4 flex justify-center">
            <div className="flex h-20 w-20 items-center justify-center rounded-[18px] bg-white p-3 shadow-lg">
              <Image
                src="/brand/provider/sumass-logo.png"
                alt="Sumass Logo"
                width={64}
                height={64}
                className="h-full w-full object-contain"
              />
            </div>
          </div>
          <h1 className="text-3xl font-bold text-[#4B236A]">
            Panel de Proveedores Test
          </h1>
          <p className="mt-2 text-[#6A6A6A]">Tu Sumass al planeta</p>
        </div>

        <div className="space-y-4">
          <div>
            <label className="mb-2 block text-sm font-medium text-[#1A1A1A]">
              Correo Electrónico
            </label>
            <input
              type="email"
              placeholder="proveedor@example.com"
              className="h-[50px] w-full rounded-[14px] border border-[#E0E0E0] px-4 focus:border-[#4B236A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-[#1A1A1A]">
              Contraseña
            </label>
            <input
              type="password"
              placeholder="••••••••"
              className="h-[50px] w-full rounded-[14px] border border-[#E0E0E0] px-4 focus:border-[#4B236A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            />
          </div>

          <button
            type="button"
            className="h-[52px] w-full rounded-xl bg-[#4B236A] text-white shadow-lg transition-all hover:bg-[#5D2B7D] hover:shadow-xl"
          >
            Ingresar
          </button>
        </div>

        <p className="mt-6 text-center text-sm text-[#6A6A6A]">
          ¿No tienes cuenta?{" "}
          <a href="#" className="font-medium text-[#4B236A] hover:underline">
            Regístrate
          </a>
        </p>
      </div>
    </div>
  );
}
